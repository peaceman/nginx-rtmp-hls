# nginx-rtmp-hls

## RTMP-Server-URL
> rtmp://$ip:$RTMP_PORT/ingest

## RTMP-StreamKey
JWT signed with the configured signing key from .env and structured as follows

Header
```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

Payload
```json
{
  "name": "foobar"
}
```

The name configured in Payload is used as public stream name.

M3U8 URL as it would be with the sample payload from above:
> http://$ip:$HTTP_PORT/foobar.m3u8
