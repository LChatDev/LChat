# Free tunnel and private networking tools

For step-by-step setup and controls, see [PORT_TOOLS.md](PORT_TOOLS.md).

| Tool | Use | Free-plan limitations |
| --- | --- | --- |
| [Cloudflare Tunnel](https://developers.cloudflare.com/tunnel/) | Publish a local website over HTTPS without opening inbound ports. | Suitable for the website; do not assume it exposes the UDP relay port range required by TURN. |
| [Playit.gg](https://playit.gg/) | Tunnel game and other server traffic. | Protocol availability varies; its [documentation](https://playit.gg/support/playit-scpsl-server/) says TCP currently requires Premium. Verify UDP and port-range support for your use. |
| [Tailscale](https://tailscale.com/pricing) | Connect your devices through a private network. | Personal plan is free; participants need access to the private network and typically install the client. |
| [ngrok](https://ngrok.com/pricing) | Publish local HTTP or TCP services. | The free tier has usage limits; [TCP requires payment-card verification](https://ngrok.com/faq). |
| [Tailscale Funnel](https://tailscale.com/docs/features/tailscale-funnel) | Public HTTPS for a local website; visitors do not install Tailscale. | Free plans supported; a `*.ts.net` name, supported TLS ports and bandwidth limits apply. |
| [LocalTunnel](https://github.com/localtunnel/localtunnel) | Temporary public HTTP/HTTPS URL for development. | Free public server; URL and availability may change, and it is not a TURN relay. Requires Node.js. |
| [localhost.run](https://github.com/localhost-run/site/blob/main/docs/cli.md) | Expose a local website using an SSH reverse tunnel. | Check current free-service and custom-domain terms; use for testing, not TURN UDP. |
| [zrok](https://blog.openziti.io/introducing-zrok-v2-0) | Public/private sharing via a hosted or self-hosted service. | Hosted free service has limits and may show an interstitial; verify current terms and application/API compatibility. |
| [bore](https://github.com/ekzhang/bore) | Minimal TCP forwarding, including the public `bore.pub` server. | No built-in HTTPS; TCP alone does not provide TURN's UDP relay ports. Public server availability is not guaranteed. |
| [frp](https://github.com/fatedier/frp) | Self-hosted TCP/UDP reverse proxy. | Software is free, but needs your own publicly reachable server. TURN needs correctly mapped relay addresses and a port range. |
| [Chisel](https://github.com/jpillora/chisel) | Self-hosted TCP/UDP tunnels. | Software is free; requires a public server. Check UDP mapping and relay-port requirements for TURN. |
| [OpenSSH reverse forwarding](https://man.openbsd.org/ssh#R) | Forward a local TCP service through your own SSH server. | Requires a reachable SSH server; a normal reverse SSH forward does not expose TURN's UDP relay range. |

## Quick testing on Windows

First start your PHP site on `127.0.0.1:8365`. LocalTunnel can provide a temporary web URL (install Node.js first):

```cmd
npx localtunnel --port 8365
```

For Tailscale Funnel, install and sign in to Tailscale on the **host**, enable Funnel according to its documentation, then run:

```cmd
tailscale funnel 8365
```

Visitors to a public Funnel URL do not need Tailscale. By contrast, private Tailscale access normally needs the client on their devices. These website tunnels do not provide a TURN relay. Temporary URLs may not suit fixed `public_url` or production use.

For a public LChat website, Cloudflare Tunnel can provide HTTPS. For TURN calls across networks, the TURN server needs a reachable listener **and UDP relay port range**; a single HTTP or TCP tunnel alone does not provide this. See [CALLS.md](CALLS.md). Pricing, eligibility and limits can change; check each provider's current terms. None of the options above has been tested with this LChat deployment.
