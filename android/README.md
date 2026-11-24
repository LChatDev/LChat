> Current downloads: see [CLIENTS.md](../CLIENTS.md). Previous application binaries have been withdrawn. The supplied APK uses a different signing key; old build/test instructions below describe retained source, not this binary.
> Актуальный APK: см. CLIENTS.md. Старые сборки удалены; подпись APK отличается. Исторические инструкции ниже относятся к сохранённым исходникам.

# LChat for Android — 3.0.0

Native Java messenger screens and native WebRTC voice calls for the LChat PHP server. Default server: https://chat.lbs.su/. Android 8.0+ (API 26), target SDK 35, package `su.lbs.lchat`, version code 30. One universal APK includes ARM64, ARMv7, x86 and x86_64. The interface uses Russian labels and preserves the Pixel design.

Download APK (previous binary withdrawn / прежняя сборка удалена) · [Русский](README_RU.md) · [Release and verification](RELEASE_3.0.0.md)

Install `LChat-3.0.0-universal.apk` over the previous official version. The signing certificate is retained. Grant microphone and notification access. No Telecom phone account is required. The normal `ACCESS_NETWORK_STATE` permission is now included and granted at installation; it prevents the reproduced native network-monitor abort.

## Native Calls v3

Android lifecycle and notification controls, the serial RTC thread, HTTP transport, audio routing, session state and cross-process lease journal are separate components. Network requests cannot block the audio queue. Late callbacks cannot revive ended calls. A completed hangup immediately releases the server participant; failed cleanup is retained for recovery. The call clock starts when a peer connects.

Microphone mute, speaker/earpiece, available input-device selection, ICE restart, background calling and return from the notification are supported. Voice calls remain native; no WebView page or additional calling server is introduced. The PHP server supplies signaling and temporary TURN credentials. Keep HTTPS and a reachable TURN server available. Native video and screen sharing are not implemented.

Incoming calls/messages use a user-visible polling foreground service rather than FCM. Android may delay polling in deep sleep or stop it after force-stop; reopen the app then. Active calls use a microphone foreground notification and a partial wake lock. Deep Doze, specific battery policies and real Bluetooth hardware require device testing.

## Messaging

Existing login/registration/email confirmation, private chats, groups, channels, bot keyboards, replies, reactions, message editing/deletion, files and voice messages are retained. The native file uploader retains its existing size limit; website features and limits may differ. This release changes the calling subsystem and does not replace the server configuration.

## Reproducible Windows build

Use JDK 21+ and Node.js for the independent APK check. Download pinned Google/Maven inputs with:

```powershell
python android/setup-tools.py
.\android\build.ps1 -JavaHome 'C:\tools\jdk-21' -BuildTools 'C:\LChat\android\sdk\build-tools\android-15' -AndroidJar 'C:\LChat\android\sdk\platform\android-35\android.jar' -WebRtcDir 'C:\LChat\android\sdk\webrtc-150.7871.08'
node android/tests/apk-integrity.cjs android/LChat-3.0.0-universal.apk
```

Adapt paths to your checkout. `build.ps1` produces `LChat.apk`, the versioned APK and its SHA-256 file; it verifies alignment and signatures including API 31/32. The downloader verifies supplier checksums. No globally installed runtime is modified.

Reuse your existing private `android/signing/` directory to update your own installations. A fresh checkout generates a different key and cannot update an APK signed with another key. Never publish `signing/`, its password, SDK downloads or server secrets. Third-party notices are included in the APK and `licenses/`. The source inherits the repository GPL license.

## Actual checks

[Release notes](RELEASE_3.0.0.md) document the Android 12 crash reproduction, native Android-to-Chromium calls through TURN, repeated entry, audio controls, background/screen-off RTP and cleanup checks. [Test instructions](tests/README.md) include host-JVM regressions and the private instrumentation runner. No claim is made of testing on physical Samsung hardware or every Android release.
