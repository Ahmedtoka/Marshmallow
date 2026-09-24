<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Something went wrong | Marshmallow</title>
    <link rel="icon" type="image/png" href="/images/logo-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #fff; color: #33307A; font-family: 'Nunito', ui-rounded, system-ui, sans-serif; line-height: 1.6; }
        .wrap { width: 100%; max-width: 560px; }
        .logo { display: block; height: 64px; width: auto; margin: 0 auto 28px; }
        .bubble { position: relative; background: #fff; border: 3px solid #E8177F; border-radius: 36px; padding: 32px 28px 36px; }
        .tail { position: absolute; right: 52px; bottom: -29px; width: 52px; height: 32px; }
        h1 { font-family: 'Fredoka', 'Nunito', system-ui, sans-serif; font-weight: 600; font-size: 2rem; line-height: 1.15; margin: 0 0 12px; }
        p { margin: 0 0 12px; color: #57548f; font-size: 1.05rem; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        a.btn { display: inline-flex; align-items: center; min-height: 48px; padding: 10px 22px; border-radius: 999px; font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 600; font-size: 1.05rem; text-decoration: none; }
        .primary { background: #E8177F; color: #fff; }
        .soft { background: #FFF5FA; color: #33307A; border: 2px solid #f1dce8; }
        a:focus-visible { outline: 3px solid #2CBCC9; outline-offset: 3px; }
        .dot { position: fixed; border-radius: 999px; z-index: -1; }
    </style>
</head>
<body>
    <span class="dot" style="width:160px;height:160px;left:-50px;top:60px;background:#F6E82B;opacity:.4"></span>
    <span class="dot" style="width:18px;height:18px;right:60px;top:40px;background:#2CBCC9"></span>
    <main class="wrap">
        <img src="/images/logo-header.png" alt="Marshmallow Child Development Center" class="logo">
        <div class="bubble">
            <h1>Something went wrong on our side</h1>
            <p>Sorry about that. Please try again in a moment. If you were sending a form, you can also reach us directly.</p>
            <div class="actions">
                <a class="btn primary" href="/">Try the homepage</a>
                <a class="btn soft" href="tel:01012666625">Call Hadayek</a>
                <a class="btn soft" href="tel:01010813332">Call Zayed</a>
            </div>
            <svg class="tail" viewBox="0 0 52 32" aria-hidden="true"><path d="M4 1.5C8 13 18 24 48 30 36 21 31 12 30 1.5" fill="#fff" stroke="#E8177F" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/></svg>
        </div>
    </main>
</body>
</html>
