<!-- EMAIL WRAPPER -->
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Notificación</title>
  <meta name="x-apple-disable-message-reformatting">
  <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
</head>
<body style="margin:0;padding:0;background:#f2f4f7;">
  <center style="width:100%;background:#f2f4f7;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width:600px;margin:0 auto;">
      <tr>
        <td style="padding:24px 16px 8px 16px;text-align:center;">
          <!-- Logo: usa UNA de estas dos opciones -->
          <!-- Opción A: URL pública -->
          <img src="{{LOGO_URL}}" width="160" height="auto" alt="Logo" style="display:block;margin:0 auto 8px auto;border:0;">
          <!-- Opción B (si usas CID): <img src="cid:logo_cid" width="160" alt="Logo" style="display:block;margin:0 auto 8px auto;border:0;"> -->
          <div style="font:14px/1.4 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#667085;">
            {{EMPRESA_NOMBRE}}
          </div>
        </td>
      </tr>

      <!-- Encabezado -->
      <tr>
        <td style="padding:0 16px 16px 16px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;">
            <tr>
              <td style="padding:20px 24px;text-align:left;">
                <h1 style="margin:0 0 6px 0;font:700 20px/1.2 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">
                  {{ASUNTO}}
                </h1>
                <p style="margin:0;font:14px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#475467;">
                  {{RESUMEN}}
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Card: Nuevo usuario -->
      <tr>
        <td style="padding:0 16px 12px 16px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;">
            <tr>
              <td style="padding:16px 20px;">
                <table role="presentation" width="100%">
                  <tr>
                    <td style="font:600 16px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">
                      👤 Nuevo usuario
                    </td>
                    <td align="right" style="font:12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#667085;">
                      {{FECHA_USUARIO}}
                    </td>
                  </tr>
                </table>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:8px">
                  <tr>
                    <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                      <strong>Nombre:</strong> {{USUARIO_NOMBRE}}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                      <strong>Email:</strong> {{USUARIO_EMAIL}}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                      <strong>Rol:</strong> {{USUARIO_ROL}}
                    </td>
                  </tr>
                </table>

                <!-- Botón -->
                <div style="margin-top:12px;">
                  <a href="{{URL_VER_USUARIO}}" style="display:inline-block;background:#6F9F53;color:#ffffff;text-decoration:none;border-radius:8px;padding:10px 16px;font:600 14px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;">
                    Ver usuario
                  </a>
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Card: Gestión de orden -->
      <tr>
        <td style="padding:0 16px 16px 16px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;">
            <tr>
              <td style="padding:16px 20px;">
                <table role="presentation" width="100%">
                  <tr>
                    <td style="font:600 16px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">
                      🧾 Orden #{{ORDEN_NUMERO}}
                    </td>
                    <td align="right" style="font:12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#667085;">
                      {{FECHA_ORDEN}}
                    </td>
                  </tr>
                </table>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:8px">
                  <tr>
                    <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                      <strong>Cliente:</strong> {{CLIENTE_NOMBRE}}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                      <strong>Estado:</strong> {{ORDEN_ESTADO}}
                    </td>
                  </tr>
                </table>

                <!-- Tabla de ítems -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:12px;border-collapse:collapse;">
                  <thead>
                    <tr>
                      <th align="left" style="padding:8px;border:1px solid #e4e7ec;font:600 12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;background:#f8f9fc;">Producto</th>
                      <th align="center" style="padding:8px;border:1px solid #e4e7ec;font:600 12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;background:#f8f9fc;">Cant.</th>
                      <th align="right" style="padding:8px;border:1px solid #e4e7ec;font:600 12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;background:#f8f9fc;">Precio</th>
                      <th align="right" style="padding:8px;border:1px solid #e4e7ec;font:600 12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;background:#f8f9fc;">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    {{ORDEN_ITEMS_ROWS}}
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3" align="right" style="padding:8px;border:1px solid #e4e7ec;font:600 13px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">Total</td>
                      <td align="right" style="padding:8px;border:1px solid #e4e7ec;font:700 14px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">{{ORDEN_TOTAL}}</td>
                    </tr>
                  </tfoot>
                </table>

                <!-- Botones -->
                <div style="margin-top:12px;">
                  <a href="{{URL_VER_ORDEN}}" style="display:inline-block;background:#0ea5e9;color:#ffffff;text-decoration:none;border-radius:8px;padding:10px 16px;font:600 14px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;margin-right:8px;">Ver orden</a>
                  <a href="{{URL_GESTIONAR_ORDEN}}" style="display:inline-block;background:#6F9F53;color:#ffffff;text-decoration:none;border-radius:8px;padding:10px 16px;font:600 14px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;">Gestionar</a>
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="padding:8px 16px 32px 16px;text-align:center;">
          <div style="font:12px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#98a2b3;">
            © {{ANIO}} {{EMPRESA_NOMBRE}} · <a href="{{URL_EMPRESA}}" style="color:#98a2b3;text-decoration:underline;">{{URL_EMPRESA_HOST}}</a>
          </div>
        </td>
      </tr>
    </table>
  </center>
</body>
</html>
