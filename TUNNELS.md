# Free tunnel and private networking tools

| Tool | Use | Free-plan limitations |
| --- | --- | --- |
| [Cloudflare Tunnel](https://developers.cloudflare.com/tunnel/) | Publish a local website over HTTPS without opening inbound ports. | Suitable for the website; do not assume it exposes the UDP relay port range required by TURN. |
| [Playit.gg](https://playit.gg/) | Tunnel game and other server traffic. | Protocol availability varies; its [documentation](https://playit.gg/support/playit-scpsl-server/) says TCP currently requires Premium. Verify UDP and port-range support for your use. |
| [Tailscale](https://tailscale.com/pricing) | Connect your devices through a private network. | Personal plan is free; participants need access to the private network and typically install the client. |
| [ngrok](https://ngrok.com/pricing) | Publish local HTTP or TCP services. | The free tier has usage limits; [TCP requires payment-card verification](https://ngrok.com/faq). |

For a public LChat website, Cloudflare Tunnel can provide HTTPS. For TURN calls across networks, the TURN server needs a reachable listener **and UDP relay port range**; a single HTTP or TCP tunnel alone does not provide this. See [CALLS.md](CALLS.md). Pricing and limits can change; check each provider's current terms.
