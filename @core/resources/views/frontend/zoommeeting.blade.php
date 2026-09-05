<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Zoom Video SDK - Laravel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      rel="stylesheet"
      href="https://source.zoom.us/uitoolkit/2.2.10-1/videosdk-ui-toolkit.css"
    />
  </head>
  <body>
    <main>
      <div id="join-flow">
        <h1>Zoom Video SDK in Laravel</h1>
        <p>Join directly inside the web app</p>
        <button id="joinSession">Join Session</button>
      </div>
      <div id="sessionContainer"></div>
    </main>

    <script src="https://source.zoom.us/uitoolkit/2.2.10-1/videosdk-ui-toolkit.min.umd.js"></script>
    <script>
      const uitoolkit = window.UIToolkit;
      const authEndpoint = "{{ url('seller/video/signature') }}";
      const sessionContainer = document.getElementById("sessionContainer");

      const config = {
        videoSDKJWT: "",
        sessionName: "{{ $sessionName }}",
        userName: "{{ $user->name ?? 'Guest' }}",
        sessionPasscode: "123", // optional
        featuresOptions: {
          virtualBackground: {
            enable: true,
          },
        },
      };

      const role = {{ $role }}; // 1 = host, 0 = attendee

      document
        .getElementById("joinSession")
        .addEventListener("click", getVideoSDKJWT);

      async function getVideoSDKJWT() {
        document.getElementById("join-flow").style.display = "none";

        const response = await fetch(authEndpoint, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
          },
          body: JSON.stringify({
            sessionName: config.sessionName,
            role: role,
          }),
        });

        const data = await response.json();

        if (data.signature) {
          config.videoSDKJWT = data.signature;
          joinSession();
        } else {
          alert("Failed to get signature");
          console.log(data);
        }
      }

      function joinSession() {
        uitoolkit.joinSession(sessionContainer, config);

        uitoolkit.onSessionClosed(() => {
          console.log("Session closed");
          document.getElementById("join-flow").style.display = "block";
        });

        uitoolkit.onSessionDestroyed(() => {
          console.log("Session destroyed");
          uitoolkit.destroy();
        });
      }
    </script>
  </body>
</html>
