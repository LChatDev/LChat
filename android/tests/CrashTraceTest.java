package su.lbs.lchat;
import java.io.*;
import java.nio.charset.StandardCharsets;
public final class CrashTraceTest {
 static byte[] text(String s){return s.getBytes(StandardCharsets.UTF_8);}
 static byte[] field(int id,byte[] data)throws IOException{ByteArrayOutputStream b=new ByteArrayOutputStream();int tag=(id<<3)|2;do{int v=tag&127;tag>>>=7;b.write(tag==0?v:v|128);}while(tag!=0);int n=data.length;do{int v=n&127;n>>>=7;b.write(n==0?v:v|128);}while(n!=0);b.write(data);return b.toByteArray();}
 static byte[] join(byte[]...parts)throws IOException{ByteArrayOutputStream b=new ByteArrayOutputStream();for(byte[] p:parts)b.write(p);return b.toByteArray();}
 public static void main(String[] args)throws Exception{
  byte[] frame=join(field(4,text("nativeStart")),field(6,text("/private/libjingle.so")));
  byte[] thread=field(4,frame);byte[] entry=join(new byte[]{8,7},field(2,thread));
  byte[] msg=join(new byte[]{48,7},field(14,text("Check failed test")),field(16,entry));
  String result=CrashTrace.extract(new ByteArrayInputStream(msg));
  if(!result.contains("Check failed test")||!result.contains("libjingle.so nativeStart")||result.contains("/private/"))throw new AssertionError(result);
  try{CrashTrace.extract(new ByteArrayInputStream(new byte[]{114,127,1}));throw new AssertionError("truncated input accepted");}catch(IOException expected){}
  System.out.println("PASS: native crash extraction, private path removal, truncated data");
 }
}
