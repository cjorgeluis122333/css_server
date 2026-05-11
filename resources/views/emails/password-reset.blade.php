<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 480px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .header {
            background-color: #1a56db;
            color: #ffffff;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        .body {
            padding: 32px;
            color: #374151;
        }
        .body p {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.6;
        }
        .code-box {
            background-color: #f0f4ff;
            border: 2px dashed #1a56db;
            border-radius: 8px;
            text-align: center;
            padding: 20px;
            margin: 24px 0;
        }
        .code-box span {
            font-size: 40px;
            font-weight: 700;
            letter-spacing: 12px;
            color: #1a56db;
        }
        .warning {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            color: #7c2d12;
        }
        .footer {
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Club Campestre de Villavicencio</h1>
        </div>
        <div class="body">
            <p>Hola,</p>
            <p>Recibimos una solicitud para restablecer la contraseña de la cuenta asociada a la acción <strong>#{{ $acc }}</strong>. Usa el siguiente código de verificación:</p>

            <div class="code-box">
                <span>{{ $code }}</span>
            </div>

            <div class="warning">
                ⏱ Este código es válido por <strong>2 minutos</strong> y solo puede usarse una vez. Si no solicitaste este cambio, ignora este correo.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Club Campestre de Villavicencio. Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
