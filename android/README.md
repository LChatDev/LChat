# LChat for Android

Native Android client for the LChat PHP server: account registration and login, private chats, groups, channels, files, voice messages, bot keyboards, and internet voice calls. The app uses native Android screens, not a WebView. The native interface currently uses Russian labels; the web interface in this repository is English. Telegram Web A styling and animations belong to the web client and are not embedded in this APK.

## Download and install

Download **LChat-2.0.8-universal.apk** from [GitHub Releases](https://github.com/LChatDev/LChat/releases/tag/android-v2.0.8). This is one installable APK, not a split APK bundle. Install it over the previous LChat app to retain its local settings and session. It uses the same package and signing certificate as the previous 2.0.3 build.

- Android 8.0 or later (min SDK 26; target SDK 35).
- ARM64, ARMv7, x86 and x86_64 are included.
- Package: `su.lbs.lchat`; version: `2.0.8`; version code: `19`.
- Default server: `https://chat.lbs.su/`. Change the address from the app menu to use another compatible LChat server.
- Grant microphone and notification permissions for calls. No Telecom phone account is required.
- The server must have working HTTPS and the matching LChat mobile API. TURN is configured by the server administrator.

The `.apk.sha256` release attachment contains the download checksum. In PowerShell, use `Get-FileHash .\LChat-2.0.8-universal.apk -Algorithm SHA256` to compare it.

## Calls and limitations

Voice calls run in a separate foreground audio process, with notification controls. Incoming calls and messages use server polling, not FCM. Android power management may delay polling or terminate the process; reopen the app after force-stop or reboot. This client supports voice calls, not native video or screen sharing.

Release checks cover the archive CRCs, DEX checksums, four native ABI libraries, Android manifest, APK alignment and signature, including verification for Android 12/12L. The signing certificate matches 2.0.3. No physical phone was available for this release: on-device installation, microphone routing and background calling have not been verified. This is not a claim of compatibility with every device.

In 2.0.8, call diagnostics can show an Android native crash summary when the OS provides one. Only the abort message and selected crash-thread symbols are extracted; no trace is sent automatically. The crash is not yet diagnosed, and this build has not been verified on a physical device.

## Build from source on Windows

Required external tools:

- JDK 21 (`javac`, `java`, `jar`, `keytool`).
- Android SDK Platform 35 (`android.jar`).
- Android SDK Build Tools 35.0.0 (`aapt2`, `zipalign`, `lib/d8.jar`, `lib/apksigner.jar`).
- Extracted `io.github.webrtc-sdk:android:150.7871.01` AAR with `classes.jar` and `jni/{arm64-v8a,armeabi-v7a,x86,x86_64}/libjingle_peerconnection_so.so`.
- Node.js to run the independent integrity test.

From the repository root:

```powershell
.\android\build.ps1 -JavaHome 'C:\tools\jdk-21' -BuildTools 'C:\tools\android-sdk\build-tools\35.0.0' -AndroidJar 'C:\tools\android-sdk\platforms\android-35\android.jar' -WebRtcDir 'C:\tools\webrtc-150.7871.01'
node .\android\tests\apk-integrity.cjs .\android\LChat-2.0.8-universal.apk
```

The script creates `LChat.apk`, the versioned universal APK and its SHA-256 file. A new checkout generates its own signing key in `android/signing/`; such a build cannot update the official APK without uninstalling it first. Keep your signing key and password private and backed up. To update your own builds, reuse your own signing directory. Official release keys are not included in this repository.

The Android sources are distributed under the repository's GPL-3.0 license. WebRTC notices are in `licenses/` and bundled in the APK. No database credentials, installation key, bot tokens or permanent TURN credentials are bundled.
