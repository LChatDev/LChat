package su.lbs.lchat;

import android.app.*;
import android.content.*;
import android.content.pm.ServiceInfo;
import android.media.*;
import android.os.*;
import org.json.*;
import org.webrtc.*;
import org.webrtc.AudioTrack;
import org.webrtc.audio.JavaAudioDeviceModule;
import java.io.*;
import java.net.*;
import java.nio.charset.StandardCharsets;
import java.util.*;
import java.util.concurrent.*;

public final class CallService extends Service {
    static final int REGISTER=1,STATE=2,SAVED=3;
    private Messenger client;
    private long clientGeneration;
    private boolean finished;
    private volatile CountDownLatch savedConnection;
    private final Messenger binder=new Messenger(new Handler(Looper.getMainLooper()){
        @Override public void handleMessage(Message message){if(message.what==REGISTER){client=message.replyTo;clientGeneration=message.getData().getLong("generation");sendState();}else if(message.what==SAVED&&Objects.equals(id,message.getData().getString("id"))&&savedConnection!=null)savedConnection.countDown();}
    });
    public static volatile boolean active=false, muted=false, speaker=false;
    public static volatile boolean closing=false, launching=false;
    public static volatile String status="";
    public static volatile String displayName="Голосовой звонок";
    public static volatile long callStartedMillis=0;
    public static volatile String incomingName="", incomingToken="";
    public static volatile String adminPresence="", adminArrival="";
    public static volatile long adminArrivalUntil=0;
    private final Set<String> seenAdmins=new HashSet<>();
    private final Set<String> ignoredCalls=new HashSet<>();
    public static volatile Runnable listener;
    private static final String CHANNEL="lchat_calls";
    private final ScheduledExecutorService loop=Executors.newSingleThreadScheduledExecutor();
    private final Handler main=new Handler(Looper.getMainLooper());
    private final Map<String,Peer> peers=new HashMap<>();
    private final ArrayList<JSONObject> outgoing=new ArrayList<>();
    private PeerConnectionFactory factory;
    private JavaAudioDeviceModule audioDevice;
    private AudioSource audioSource;
    private AudioTrack audioTrack;
    private AudioManager audio;
    private AudioFocusRequest focus;
    private PowerManager.WakeLock wake;
    private String base,token,id,cookie,csrf,endpoint;
    private long after=0;
    private int failures=0;
    private volatile boolean stopping=false;
    private volatile boolean cancelRequested=false;
    private volatile HttpURLConnection activeRequest;

    private boolean flushScheduled=false;
    private int signalFailures=0;
    private List<PeerConnection.IceServer> ice=new ArrayList<>();
    private PeerConnection.IceTransportsType transport=PeerConnection.IceTransportsType.ALL;

    private static final class Peer {
        String id,name;
        PeerConnection pc;
        final ArrayList<IceCandidate> candidates=new ArrayList<>();
        boolean offered=false,negotiating=false,connected=false;
        int restarts=0;
        ScheduledFuture<?> retry;
    }
    private static class Sdp implements SdpObserver {
        @Override public void onCreateSuccess(SessionDescription s){}
        @Override public void onSetSuccess(){}
        @Override public void onCreateFailure(String e){}
        @Override public void onSetFailure(String e){}
    }
    @Override public void onCreate(){
        super.onCreate();CallDiagnostics.install(this);CallDiagnostics.event("AUDIO_SERVICE_CREATED");
        NotificationManager manager=getSystemService(NotificationManager.class);
        manager.createNotificationChannel(new NotificationChannel(CHANNEL,"Голосовые звонки",NotificationManager.IMPORTANCE_LOW));
    }
    @Override public int onStartCommand(Intent intent,int flags,int startId){
        if(intent==null){stopSelf();return START_NOT_STICKY;}
        String command=intent.getStringExtra("command");
        if("end".equals(command)){cancelRequested=true;HttpURLConnection request=activeRequest;if(request!=null)request.disconnect();try{loop.execute(()->stopCall("Звонок завершён"));}catch(RejectedExecutionException ignored){}return START_NOT_STICKY;}
        if("mute-state".equals(command)){boolean value=intent.getBooleanExtra("muted",false);submit(()->{muted=value;if(audioTrack!=null)audioTrack.setEnabled(!muted);});return START_NOT_STICKY;}
        if("decline".equals(command)){submit(()->{if(!incomingToken.isEmpty())ignoredCalls.add(incomingToken);incomingToken="";incomingName="";publish(status);});return START_NOT_STICKY;}
        if("input".equals(command)){int device=intent.getIntExtra("device",0);submit(()->changeInput(device));return START_NOT_STICKY;}
        if("mic".equals(command)){submit(()->{muted=!muted;if(audioTrack!=null)audioTrack.setEnabled(!muted);publish(status);});return START_NOT_STICKY;}
        if("speaker".equals(command)){submit(()->{speaker=!speaker;if(audio!=null)audio.setSpeakerphoneOn(speaker);publish(status);});return START_NOT_STICKY;}
        if(!"start".equals(command))return START_NOT_STICKY;CallDiagnostics.event("ENGINE_START_RECEIVED");launching=false;if(active)return START_NOT_STICKY;
        if(stopping||closing){ToastUtil.show(this,"Предыдущий звонок ещё завершается");return START_NOT_STICKY;}
        base=intent.getStringExtra("base");token=intent.getStringExtra("token");endpoint=intent.getStringExtra("endpoint");cookie=intent.getStringExtra("cookie");
        if(base==null||!base.startsWith("https://")||token==null||!token.matches("[a-f0-9]{48}")||!("api.php".equals(endpoint)||"admin.php".equals(endpoint))){CallDiagnostics.event("INVALID_CALL_REQUEST");finished=true;status="Некорректные параметры звонка";notifyUi();stopSelf();return START_NOT_STICKY;}
        active=true;callStartedMillis=System.currentTimeMillis();displayName="Голосовой звонок";muted=intent.getBooleanExtra("listen",false);speaker=false;status="Подключаемся…";
        try{
            if(Build.VERSION.SDK_INT>=30)startForeground(7,notification(status),ServiceInfo.FOREGROUND_SERVICE_TYPE_MICROPHONE);else startForeground(7,notification(status));
        }catch(Exception e){CallDiagnostics.event("FOREGROUND_FAILED",e);active=false;finished=true;status="Не разрешён доступ к микрофону в фоне";notifyUi();stopSelf();return START_NOT_STICKY;}
        CallDiagnostics.event("FOREGROUND_READY");publish(status);submit(this::startCall);return START_NOT_STICKY;
    }
    private Notification notification(String text){
        PendingIntent open=PendingIntent.getActivity(this,0,new Intent(this,NativeActivity.class).setAction("system-call"),PendingIntent.FLAG_UPDATE_CURRENT|PendingIntent.FLAG_IMMUTABLE);
        PendingIntent end=PendingIntent.getService(this,1,new Intent(this,CallService.class).putExtra("command","end"),PendingIntent.FLAG_UPDATE_CURRENT|PendingIntent.FLAG_IMMUTABLE);
        Notification.Builder builder=new Notification.Builder(this,CHANNEL).setSmallIcon(R.drawable.ic_lchat).setContentTitle(displayName).setContentText(text).setContentIntent(open).setOngoing(true).setOnlyAlertOnce(true).setCategory(Notification.CATEGORY_CALL).setVisibility(Notification.VISIBILITY_PRIVATE);

        if(Build.VERSION.SDK_INT>=31)builder.setStyle(Notification.CallStyle.forOngoingCall(new Person.Builder().setName(displayName).setImportant(true).build(),end));else builder.addAction(new Notification.Action.Builder(null,"Завершить",end).build());
        PendingIntent mic=PendingIntent.getService(this,2,new Intent(this,CallService.class).putExtra("command","mic"),PendingIntent.FLAG_UPDATE_CURRENT|PendingIntent.FLAG_IMMUTABLE);builder.addAction(new Notification.Action.Builder(null,muted?"Включить микрофон":"Выключить микрофон",mic).build());
        PendingIntent route=PendingIntent.getService(this,3,new Intent(this,CallService.class).putExtra("command","speaker"),PendingIntent.FLAG_UPDATE_CURRENT|PendingIntent.FLAG_IMMUTABLE);builder.addAction(new Notification.Action.Builder(null,speaker?"Выключить динамик":"Включить динамик",route).build());return builder.build();
    }
    private void notifyUi(){main.post(this::sendState);}
    private void sendState(){
        if(client==null)return;Bundle state=new Bundle();state.putLong("generation",clientGeneration);
        state.putBoolean("active",active);state.putBoolean("launching",launching);state.putBoolean("closing",closing);state.putBoolean("finished",finished);
        state.putBoolean("muted",muted);state.putBoolean("speaker",speaker);state.putString("status",status);state.putString("name",displayName);
        state.putString("incoming_token",incomingToken);state.putString("incoming_name",incomingName);state.putString("admins",adminPresence);state.putString("arrival",adminArrival);
        state.putLong("arrival_until",adminArrivalUntil);state.putLong("started",callStartedMillis);
        state.putString("id",id==null?"":id);state.putString("token",token==null?"":token);state.putString("base",base==null?"":base);
        try{Message message=Message.obtain(null,STATE);message.setData(state);client.send(message);}catch(RemoteException e){client=null;}
    }
    private void changeInput(int device){
        if(audioDevice==null||audio==null)return;
        try{
            if(device==0){audioDevice.setPreferredInputDevice(null);if(Build.VERSION.SDK_INT>=31)audio.clearCommunicationDevice();publish("Микрофон: автоматически");return;}
            for(AudioDeviceInfo input:audio.getDevices(AudioManager.GET_DEVICES_INPUTS))if(input.getId()==device){
                if(input.getType()==AudioDeviceInfo.TYPE_BLUETOOTH_SCO){if(Build.VERSION.SDK_INT>=31){for(AudioDeviceInfo output:audio.getAvailableCommunicationDevices())if(output.getType()==AudioDeviceInfo.TYPE_BLUETOOTH_SCO){audio.setCommunicationDevice(output);break;}}else{audio.startBluetoothSco();audio.setBluetoothScoOn(true);}}
                audioDevice.setPreferredInputDevice(input);publish("Микрофон: "+input.getProductName());return;
            }
            publish("Микрофон отключён от телефона. Выберите другой.");
        }catch(Exception e){publish("Не удалось переключить микрофон. Проверьте разрешения и подключение.");}
    }
    private void publish(String text){status=text;if(active&&!stopping)getSystemService(NotificationManager.class).notify(7,notification(text+(adminPresence.isEmpty()?"":" · Администраторы: "+adminPresence)));notifyUi();}
    private void submit(Runnable task){try{loop.execute(()->{if(!stopping&&!cancelRequested)try{task.run();}catch(Exception e){CallDiagnostics.event("AUDIO_WORKER_FAILED",e);stopCall("Ошибка аудио. Подключение освобождено");}});}catch(RejectedExecutionException ignored){}}
    private JSONObject object(Object...args){JSONObject j=new JSONObject();try{for(int i=0;i<args.length;i+=2)j.put((String)args[i],args[i+1]);}catch(JSONException e){throw new IllegalArgumentException(e);}return j;}
    private static final class ApiError extends IOException {final int code;ApiError(int code,String message){super(message);this.code=code;}}
    private JSONObject request(String file,String action,JSONObject data) throws Exception {
        URL url=new URL(base+file+"?action="+URLEncoder.encode(action,"UTF-8"));
        HttpURLConnection connection=(HttpURLConnection)url.openConnection();activeRequest=connection;connection.setInstanceFollowRedirects(false);connection.setConnectTimeout(8000);connection.setReadTimeout(8000);connection.setRequestProperty("Cookie",cookie==null?"":cookie);connection.setRequestProperty("Cache-Control","no-store");
        try{
            if(data!=null){connection.setRequestMethod("POST");connection.setDoOutput(true);connection.setRequestProperty("Content-Type","application/json");connection.setRequestProperty("X-CSRF-Token",csrf==null?"":csrf);byte[] bytes=data.toString().getBytes(StandardCharsets.UTF_8);connection.setFixedLengthStreamingMode(bytes.length);try(OutputStream out=connection.getOutputStream()){out.write(bytes);}}
            int code=connection.getResponseCode();InputStream stream=code>=400?connection.getErrorStream():connection.getInputStream();if(stream==null)throw new ApiError(code,"Сервер не ответил");
            ByteArrayOutputStream out=new ByteArrayOutputStream();byte[] chunk=new byte[4096];int n,total=0;try(InputStream in=stream){while((n=in.read(chunk))!=-1){total+=n;if(total>1048576)throw new IOException("Слишком большой ответ сервера");out.write(chunk,0,n);}}
            JSONObject body=new JSONObject(out.toString("UTF-8"));if(code<200||code>=300)throw new ApiError(code,body.optString("error","Ошибка сервера"));return body;
        }finally{connection.disconnect();if(activeRequest==connection)activeRequest=null;}
    }
    private JSONObject api(String action,JSONObject data)throws Exception{return request(endpoint,action,data);}
    private JavaAudioDeviceModule createAudioDevice(){
        boolean hardwareEffects=Build.VERSION.SDK_INT>28;
        CallDiagnostics.event(hardwareEffects?"AUDIO_EFFECTS_HARDWARE":"AUDIO_EFFECTS_SOFTWARE");
        return JavaAudioDeviceModule.builder(this)
            .setUseHardwareAcousticEchoCanceler(hardwareEffects).setUseHardwareNoiseSuppressor(hardwareEffects)
            .setUseLowLatency(false).setEnableVolumeLogger(false)
            .setAudioRecordErrorCallback(new JavaAudioDeviceModule.AudioRecordErrorCallback(){
                public void onWebRtcAudioRecordInitError(String ignored){audioError("MIC_INIT_ERROR");}
                public void onWebRtcAudioRecordStartError(JavaAudioDeviceModule.AudioRecordStartErrorCode code,String ignored){audioError("MIC_START_ERROR");}
                public void onWebRtcAudioRecordError(String ignored){audioError("MIC_RUNTIME_ERROR");}
            })
            .setAudioTrackErrorCallback(new JavaAudioDeviceModule.AudioTrackErrorCallback(){
                public void onWebRtcAudioTrackInitError(String ignored){audioError("SPEAKER_INIT_ERROR");}
                public void onWebRtcAudioTrackStartError(JavaAudioDeviceModule.AudioTrackStartErrorCode code,String ignored){audioError("SPEAKER_START_ERROR");}
                public void onWebRtcAudioTrackError(String ignored){audioError("SPEAKER_RUNTIME_ERROR");}
            })
            .setAudioRecordStateCallback(new JavaAudioDeviceModule.AudioRecordStateCallback(){
                public void onWebRtcAudioRecordStart(){CallDiagnostics.event("MIC_RUNNING");}
                public void onWebRtcAudioRecordStop(){CallDiagnostics.event("MIC_STOPPED");}
            })
            .setAudioTrackStateCallback(new JavaAudioDeviceModule.AudioTrackStateCallback(){
                public void onWebRtcAudioTrackStart(){CallDiagnostics.event("SPEAKER_RUNNING");}
                public void onWebRtcAudioTrackStop(){CallDiagnostics.event("SPEAKER_STOPPED");}
            }).createAudioDeviceModule();
    }
    private void audioError(String stage){CallDiagnostics.event(stage);submit(()->stopCall("Не удалось запустить микрофон или динамик. Подключение освобождено."));}
    private void startCall(){
        try{
            CallDiagnostics.event("API_ME");JSONObject me=request("api.php","me",null);if(me.isNull("user"))throw new IOException("Сначала войдите в аккаунт");csrf=me.getString("csrf");
            CallLease.recover(this,base,cookie);if(cancelRequested)return;
            CallDiagnostics.event("API_ROOM_JOIN");JSONObject joined=api("room.join",object("token",token,"listen",muted));id=joined.getString("id");CallDiagnostics.event("ROOM_JOINED");CallLease.remember(this,base,token,id);savedConnection=new CountDownLatch(1);notifyUi();
            if(!savedConnection.await(3,TimeUnit.SECONDS))throw new IOException("Не удалось сохранить состояние звонка");
            CallDiagnostics.event("CONNECTION_SAVED_BY_APP");if(cancelRequested)return;
            JSONArray servers=joined.getJSONArray("ice_servers");for(int i=0;i<servers.length();i++){JSONObject s=servers.getJSONObject(i);Object urls=s.get("urls");ArrayList<String> addresses=new ArrayList<>();if(urls instanceof JSONArray){JSONArray a=(JSONArray)urls;for(int k=0;k<a.length();k++)addresses.add(a.getString(k));}else addresses.add(urls.toString());ice.add(PeerConnection.IceServer.builder(addresses).setUsername(s.optString("username","")).setPassword(s.optString("credential","")).createIceServer());}
            transport="relay".equals(joined.optString("ice_transport_policy"))?PeerConnection.IceTransportsType.RELAY:PeerConnection.IceTransportsType.ALL;
            audio=getSystemService(AudioManager.class);audio.setMode(AudioManager.MODE_IN_COMMUNICATION);
            AudioAttributes attributes=new AudioAttributes.Builder().setUsage(AudioAttributes.USAGE_VOICE_COMMUNICATION).setContentType(AudioAttributes.CONTENT_TYPE_SPEECH).build();
            focus=new AudioFocusRequest.Builder(AudioManager.AUDIOFOCUS_GAIN).setAudioAttributes(attributes).setOnAudioFocusChangeListener(change->{if(change==AudioManager.AUDIOFOCUS_LOSS)submit(()->stopCall("Аудио используется другим приложением"));}).build();
            audio.requestAudioFocus(focus);
            wake=getSystemService(PowerManager.class).newWakeLock(PowerManager.PARTIAL_WAKE_LOCK,"LChat:Call");wake.acquire(7*60*60*1000L);
            CallDiagnostics.event("WEBRTC_INITIALIZE");PeerConnectionFactory.initialize(PeerConnectionFactory.InitializationOptions.builder(getApplicationContext()).createInitializationOptions());
            audioDevice=createAudioDevice();
            factory=PeerConnectionFactory.builder().setAudioDeviceModule(audioDevice).createPeerConnectionFactory();
            audioSource=factory.createAudioSource(new MediaConstraints());audioTrack=factory.createAudioTrack("lchat_audio",audioSource);audioTrack.setEnabled(!muted);
            CallDiagnostics.event("AUDIO_READY");publish("Вы в звонке · ждём участников");poll();
        }catch(Exception|LinkageError e){CallDiagnostics.event(e instanceof ApiError?"API_ERROR_"+((ApiError)e).code:"AUDIO_START_FAILED",e);stopCall(e instanceof ApiError?e.getMessage():"Не удалось начать звонок. Проверьте HTTPS, сеть и разрешение микрофона.");}
    }
    private void poll(){
        if(stopping||cancelRequested)return;
        try{
            JSONObject data=api("room.poll",object("token",token,"id",id,"after",after,"muted",muted,"camera",false));if(cancelRequested||stopping)return;failures=0;
            incomingToken="";incomingName="";JSONArray waiting=data.optJSONArray("incoming_calls");
            if(waiting!=null)for(int w=0;w<waiting.length();w++){JSONObject call=waiting.getJSONObject(w);if(!ignoredCalls.contains(call.getString("token"))){incomingToken=call.getString("token");incomingName=call.getString("caller");break;}}
            JSONArray list=data.getJSONArray("peers");Set<String> ids=new HashSet<>();StringBuilder admins=new StringBuilder();
            for(int i=0;i<list.length();i++){JSONObject info=list.getJSONObject(i);String pid=info.getString("id");ids.add(pid);if(info.optInt("is_admin")==1){String name=info.getString("name");if(admins.length()>0)admins.append("\n");admins.append(name).append(info.optInt("tile_hidden")==1?" · без видеоплитки":"");if(seenAdmins.add(pid)){adminArrival="Админ зашёл в звонок: "+name;adminArrivalUntil=System.currentTimeMillis()+10000;}}}
            displayName=list.length()==1?list.getJSONObject(0).getString("name"):list.length()>1?"Групповой звонок · "+(list.length()+1):"Голосовой звонок";
            adminPresence=admins.toString();if(adminPresence.isEmpty()||System.currentTimeMillis()>=adminArrivalUntil)adminArrival="";
            for(String old:new ArrayList<>(peers.keySet()))if(!ids.contains(old))removePeer(old);
            for(int i=0;i<list.length();i++){JSONObject info=list.getJSONObject(i);String pid=info.getString("id");Peer p=peers.get(pid);if(p==null){p=createPeer(pid,info.getString("name"));peers.put(pid,p);}if(id.compareTo(pid)<0&&!p.offered)offer(p,false);}
            JSONArray signals=data.getJSONArray("signals");for(int i=0;i<signals.length();i++){JSONObject signal=signals.getJSONObject(i);Peer p=peers.get(signal.getString("sender"));if(p!=null)handleSignal(p,signal.getJSONObject("payload"));after=Math.max(after,signal.getLong("id"));}
            long connected=peers.values().stream().filter(p->p.connected).count();publish("В звонке: "+(peers.size()+1)+" · На связи: "+connected+(incomingName.isEmpty()?"":" · Входящий: "+incomingName));
        }catch(Exception e){failures++;if(e instanceof ApiError&&Arrays.asList(401,403,404,410).contains(((ApiError)e).code)){stopCall(e.getMessage());return;}publish("Восстанавливаем связь с сервером…");if(failures>15){stopCall("Сеть недоступна. Войдите в звонок заново.");return;}}
        if(!stopping&&!cancelRequested)loop.schedule(this::poll,failures>0?3000:1000,TimeUnit.MILLISECONDS);
    }
    private Peer createPeer(String pid,String name){
        Peer p=new Peer();p.id=pid;p.name=name;
        PeerConnection.RTCConfiguration config=new PeerConnection.RTCConfiguration(ice);config.sdpSemantics=PeerConnection.SdpSemantics.UNIFIED_PLAN;config.iceTransportsType=transport;
        p.pc=factory.createPeerConnection(config,new PeerConnection.Observer(){
            @Override public void onSignalingChange(PeerConnection.SignalingState state){}
            @Override public void onIceConnectionChange(PeerConnection.IceConnectionState state){}
            @Override public void onIceConnectionReceivingChange(boolean r){}
            @Override public void onIceGatheringChange(PeerConnection.IceGatheringState state){}
            @Override public void onIceCandidate(IceCandidate c){submit(()->send(p,object("type","candidate","candidate",object("candidate",c.sdp,"sdpMid",c.sdpMid,"sdpMLineIndex",c.sdpMLineIndex))));}
            @Override public void onIceCandidatesRemoved(IceCandidate[] c){}
            @Override public void onAddStream(MediaStream stream){}
            @Override public void onRemoveStream(MediaStream stream){}
            @Override public void onDataChannel(DataChannel channel){}
            @Override public void onRenegotiationNeeded(){}
            // Remote audio tracks are enabled by default. Do not access JNI wrappers
            // on this callback thread while the signalling thread may replace them.
            @Override public void onTrack(RtpTransceiver t){CallDiagnostics.event("REMOTE_AUDIO_TRACK");}
            @Override public void onConnectionChange(PeerConnection.PeerConnectionState state){submit(()->{p.connected=state==PeerConnection.PeerConnectionState.CONNECTED;if(p.connected){CallDiagnostics.event("PEER_CONNECTED");p.restarts=0;if(p.retry!=null)p.retry.cancel(false);p.retry=null;}else if(state==PeerConnection.PeerConnectionState.FAILED||state==PeerConnection.PeerConnectionState.DISCONNECTED)recover(p);});}
        });
        if(p.pc==null)throw new IllegalStateException("Cannot create peer connection");
        if(id.compareTo(pid)<0)p.pc.addTransceiver(audioTrack,new RtpTransceiver.RtpTransceiverInit(RtpTransceiver.RtpTransceiverDirection.SEND_RECV,Collections.singletonList("lchat")));
        recover(p);return p;
    }
    private void offer(Peer p,boolean restart){
        if(p.negotiating||stopping||(!restart&&p.offered))return;p.negotiating=true;
        MediaConstraints constraints=new MediaConstraints();if(restart)constraints.mandatory.add(new MediaConstraints.KeyValuePair("IceRestart","true"));
        Runnable create=()->p.pc.createOffer(new Sdp(){@Override public void onCreateSuccess(SessionDescription s){submit(()->localDescription(p,s));}@Override public void onCreateFailure(String e){submit(()->{p.negotiating=false;recover(p);});}},constraints);
        if(restart&&p.pc.signalingState()==PeerConnection.SignalingState.HAVE_LOCAL_OFFER)p.pc.setLocalDescription(new Sdp(){@Override public void onSetSuccess(){submit(create);}@Override public void onSetFailure(String e){submit(()->{p.negotiating=false;recover(p);});}},new SessionDescription(SessionDescription.Type.ROLLBACK,""));else create.run();
    }
    private void localDescription(Peer p,SessionDescription s){
        if(stopping||!peers.containsKey(p.id))return;
        p.pc.setLocalDescription(new Sdp(){@Override public void onSetSuccess(){submit(()->{p.negotiating=false;p.offered=true;send(p,object("type",s.type.canonicalForm(),"sdp",s.description));});}@Override public void onSetFailure(String e){submit(()->{p.negotiating=false;recover(p);});}},s);
    }
    private void handleSignal(Peer p,JSONObject message)throws JSONException{
        String type=message.getString("type");
        if("restart".equals(type)){if(id.compareTo(p.id)<0)offer(p,true);return;}
        if("candidate".equals(type)){JSONObject c=message.getJSONObject("candidate");IceCandidate candidate=new IceCandidate(c.optString("sdpMid",null),c.optInt("sdpMLineIndex",0),c.getString("candidate"));if(p.pc.getRemoteDescription()!=null)p.pc.addIceCandidate(candidate);else p.candidates.add(candidate);return;}
        if(!Arrays.asList("offer","answer").contains(type))return;
        if("offer".equals(type)&&id.compareTo(p.id)<0)return;
        if("answer".equals(type)&&p.pc.signalingState()!=PeerConnection.SignalingState.HAVE_LOCAL_OFFER)return;
        SessionDescription description=new SessionDescription(SessionDescription.Type.fromCanonicalForm(type),message.getString("sdp"));
        p.pc.setRemoteDescription(new Sdp(){@Override public void onSetSuccess(){submit(()->{
            if(stopping||!peers.containsKey(p.id))return;
            for(IceCandidate c:p.candidates)p.pc.addIceCandidate(c);p.candidates.clear();
            if("offer".equals(type)){
                for(RtpTransceiver t:p.pc.getTransceivers()){
                    if(t.getMediaType()==MediaStreamTrack.MediaType.MEDIA_TYPE_AUDIO){t.setDirection(RtpTransceiver.RtpTransceiverDirection.SEND_RECV);t.getSender().setTrack(audioTrack,false);t.getSender().setStreams(Collections.singletonList("lchat"));}
                    else t.setDirection(RtpTransceiver.RtpTransceiverDirection.INACTIVE);
                }
                p.pc.createAnswer(new Sdp(){@Override public void onCreateSuccess(SessionDescription s){submit(()->localDescription(p,s));}@Override public void onCreateFailure(String e){submit(()->recover(p));}},new MediaConstraints());
            }
        });}@Override public void onSetFailure(String e){submit(()->recover(p));}},description);
    }
    private void send(Peer p,JSONObject payload){
        if(stopping||!peers.containsKey(p.id))return;outgoing.add(object("to",p.id,"payload",payload));
        if(!flushScheduled){flushScheduled=true;loop.schedule(this::flush,30,TimeUnit.MILLISECONDS);}
    }
    private void flush(){
        flushScheduled=false;if(stopping||outgoing.isEmpty())return;JSONArray batch=new JSONArray();int size=0;
        while(!outgoing.isEmpty()&&batch.length()<24){JSONObject packet=outgoing.get(0);int bytes=packet.toString().getBytes(StandardCharsets.UTF_8).length;if(batch.length()>0&&size+bytes>50000)break;outgoing.remove(0);batch.put(packet);size+=bytes;}
        long delay=30;
        try{
            JSONArray results=api("room.signals",object("token",token,"id",id,"packets",batch)).getJSONArray("results");
            if(results.length()!=batch.length())throw new IOException("Incomplete signaling response");
            signalFailures=0;
            for(int i=0;i<results.length();i++){
                JSONObject result=results.getJSONObject(i);
                if(!result.optBoolean("ok")){
                    CallDiagnostics.event("SIGNAL_REJECTED_"+result.optInt("status"));
                    Peer peer=peers.get(batch.getJSONObject(i).getString("to"));
                    if(peer!=null)recover(peer);
                }
            }
        }catch(Exception e){
            if(e instanceof ApiError&&Arrays.asList(401,403,404,410).contains(((ApiError)e).code)){stopCall(e.getMessage());return;}
            CallDiagnostics.event("SIGNAL_SEND_FAILED",e);
            publish("Восстанавливаем сигнализацию…");
            if(++signalFailures<=3){
                for(int i=batch.length()-1;i>=0;i--)outgoing.add(0,batch.optJSONObject(i));
                delay=Math.min(5000,1000L*signalFailures);
            }else{
                signalFailures=0;
                for(Peer p:peers.values())recover(p);
            }
        }
        if(!outgoing.isEmpty()&&!stopping){flushScheduled=true;loop.schedule(this::flush,delay,TimeUnit.MILLISECONDS);}
    }
    private void recover(Peer p){
        if(stopping||p.retry!=null)return;
        p.retry=loop.schedule(()->{p.retry=null;if(stopping||p.connected||!peers.containsKey(p.id))return;if(p.restarts++>=2){publish("Связь с участником потеряна. Повторите вход.");return;}if(id.compareTo(p.id)<0)offer(p,true);else send(p,object("type","restart"));recover(p);},10,TimeUnit.SECONDS);
    }
    private void removePeer(String pid){Peer p=peers.remove(pid);if(p!=null){if(p.retry!=null)p.retry.cancel(false);attempt(()->p.pc.close());attempt(()->p.pc.dispose());}}
    private void attempt(Runnable operation){try{operation.run();}catch(Exception e){android.util.Log.w("LChat","Audio cleanup failed",e);}}
    private void releaseLease(){if(id!=null)try{MobileApi.requestSessionless(base,cookie,"mobile.release",object("token",token,"id",id),csrf);CallLease.clear(this,id);CallDiagnostics.event("CONNECTION_RELEASED");}catch(Exception e){CallDiagnostics.event("CONNECTION_RELEASE_DEFERRED",e);}}
    private void stopCall(String text){
        if(stopping)return;stopping=true;closing=true;
        try{cleanup();}finally{active=false;status=text;notifyUi();}
        try{releaseLease();}finally{cookie=null;csrf=null;main.post(()->{closing=false;finished=true;sendState();stopForeground(STOP_FOREGROUND_REMOVE);ToastUtil.show(this,text);stopSelf();});}
    }
    private void cleanup(){
        for(String pid:new ArrayList<>(peers.keySet()))removePeer(pid);
        if(audioTrack!=null){attempt(()->audioTrack.dispose());audioTrack=null;}if(audioSource!=null){attempt(()->audioSource.dispose());audioSource=null;}
        if(factory!=null){attempt(()->factory.dispose());factory=null;}if(audioDevice!=null){attempt(()->audioDevice.release());audioDevice=null;}
        if(audio!=null){attempt(()->{if(Build.VERSION.SDK_INT>=31)audio.clearCommunicationDevice();else{audio.stopBluetoothSco();audio.setBluetoothScoOn(false);}});attempt(()->audio.setSpeakerphoneOn(false));attempt(()->audio.setMode(AudioManager.MODE_NORMAL));if(focus!=null)attempt(()->audio.abandonAudioFocusRequest(focus));}
        if(wake!=null&&wake.isHeld())attempt(()->wake.release());outgoing.clear();
        incomingName="";incomingToken="";ignoredCalls.clear();adminPresence="";adminArrival="";adminArrivalUntil=0;seenAdmins.clear();
    }
    @Override public void onDestroy(){
        if(!stopping){stopping=true;closing=true;cancelRequested=true;HttpURLConnection request=activeRequest;if(request!=null)request.disconnect();try{loop.execute(()->{try{cleanup();}finally{releaseLease();cookie=null;csrf=null;closing=false;}});}catch(RejectedExecutionException ignored){closing=false;}}else closing=false;
        loop.shutdown();active=false;launching=false;notifyUi();super.onDestroy();
    }
    @Override public IBinder onBind(Intent intent){return binder.getBinder();}
    private static final class ToastUtil {static void show(Context c,String text){android.widget.Toast.makeText(c,text,android.widget.Toast.LENGTH_LONG).show();}}
}
