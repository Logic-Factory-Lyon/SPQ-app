<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #4f46e5; padding: 32px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .content { padding: 36px 40px; color: #374151; font-size: 15px; line-height: 1.7; }
        .content p { margin: 0 0 16px; }
        .btn { display: inline-block; background: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 8px 0 20px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 40px; color: #6b7280; font-size: 13px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>SPQ</h1>
        </div>
        <div class="content">
            {!! $body !!}
        </div>
        @if($footer)
        <div class="footer">
            {!! $footer !!}
        </div>
        @endif
    </div>
</body>
</html>
