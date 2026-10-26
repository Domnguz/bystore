<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifica tu correo en ByteStore</title>
</head>
<body style="margin:0;padding:0;background-color:#f2f6f9;font-family:Arial,Helvetica,sans-serif;color:#182c3b;">
    <div style="display:none;font-size:1px;color:#f2f6f9;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">Un último paso para dejar tu cuenta de ByteStore lista.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f2f6f9;">
        <tr><td align="center" style="padding:32px 12px;">
            <table role="presentation" width="560" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:560px;background-color:#ffffff;border:1px solid #e1e9ef;border-radius:12px;overflow:hidden;">
                <tr><td style="padding:26px 28px;background-color:#082a3d;border-bottom:3px solid #29a7e8;border-radius:12px 12px 0 0;">
                    <img src="{{ $message->embed(public_path('img/ByteStore-sindescripcion.png')) }}" alt="ByteStore" width="190" style="display:block;width:190px;max-width:100%;height:auto;border:0;">
                    <p style="margin:12px 0 0;font-size:10px;line-height:16px;letter-spacing:1.5px;color:#b6e5fb;">COMPONENTES Y SERVICIO TÉCNICO</p>
                </td></tr>
                <tr><td style="padding:30px 28px;">
                    <p style="margin:0 0 12px;font-size:12px;line-height:18px;font-weight:bold;color:#087caa;">BIENVENIDO A BYTESTORE</p>
                    <h1 style="margin:0 0 24px;font-size:28px;line-height:35px;letter-spacing:-0.7px;font-weight:bold;color:#082a3d;">Confirma tu correo<br>y deja tu cuenta lista.</h1>
                    <p style="margin:0 0 12px;font-size:15px;line-height:24px;">Hola, {{ $nombre }}:</p>
                    <p style="margin:0 0 24px;font-size:15px;line-height:24px;color:#526575;">Gracias por crear tu cuenta. Verifica tu correo para comprar en ByteStore y consultar tus pedidos desde un solo lugar.</p>
                    <p style="margin:0 0 12px;font-size:14px;line-height:22px;color:#526575;">Escribe este código en la pantalla de verificación de ByteStore:</p>
                    <div style="padding:20px 12px;background-color:#eef7fc;border:1px solid #c5e5f5;border-radius:8px;text-align:center;font-family:monospace;font-size:36px;line-height:44px;font-weight:bold;letter-spacing:8px;color:#082a3d;">{{ $codigo }}</div>
                    <p style="margin:20px 0 0;font-size:13px;line-height:21px;color:#526575;">El código vence en <strong>{{ $expiresMinutes }} minutos</strong> y solo puede usarse una vez. Si solicitas otro, usa el último código recibido.</p>
                    <p style="margin:20px 0 0;font-size:12px;line-height:20px;color:#647787;">No compartas este código con nadie.</p>
                </td></tr>
                <tr><td style="padding:20px 28px;background-color:#f8fafc;border-top:1px solid #e7edf2;border-radius:0 0 12px 12px;">
                    <p style="margin:0;font-size:12px;line-height:20px;color:#647787;">Si no creaste esta cuenta, puedes ignorar este correo.</p>
                    <p style="margin:8px 0 0;font-size:12px;line-height:20px;font-weight:bold;color:#082a3d;">El equipo de ByteStore</p>
                </td></tr>
            </table>
            <p style="margin:20px 0 0;font-size:11px;line-height:18px;color:#647787;">ByteStore · Todo para tu PC.</p>
        </td></tr>
    </table>
</body>
</html>
