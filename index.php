<?php

/**
 * index.php — Alex Roofing LLC
 * Roofing services (single-page, no external dependencies)
 */

// -------------------------
// Site configuration
// -------------------------
$siteName = "Alex Roofing LLC";
$tagline  = "Residential & light commercial roofing — repairs, replacements, inspections";
$address  = "P.O. Box 272427, Houston, TX 77277, USA";
$phones   = ["7138570449", "7136220260"];
$emailTo  = "hello@Alexroofingllc.com"; // change to your real inbox

// IMPORTANT: replace after deploy (used for canonical + og)
$siteUrl = "https://example.com";
$canonicalUrl = rtrim($siteUrl, "/") . "/";

// -------------------------
// Helpers
// -------------------------
function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }
function tel_clean($p) { return preg_replace('/[^0-9+]/', '', (string)$p); }

// -------------------------
// Contact form handling
// -------------------------
$form = ["name"=>"","email"=>"","phone"=>"","service"=>"","message"=>""];
$errors = [];
$success = false;

// bootstrap.php already starts session; do NOT call session_start() here.
if (!isset($_SESSION["csrf_token"]) || !is_string($_SESSION["csrf_token"]) || strlen($_SESSION["csrf_token"]) < 20) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"];

if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST" && isset($_POST["contact_form"])) {

  // CSRF
  $postedToken = $_POST["csrf_token"] ?? "";
  if (!$postedToken || !hash_equals($csrfToken, $postedToken)) {
    $errors[] = "Security check failed. Please refresh and try again.";
  }

  // Honeypot (bots fill it; humans won't see it)
  $honeypot = trim($_POST["company_site"] ?? "");
  if ($honeypot !== "") {
    $errors[] = "Submission rejected.";
  }

  // Collect
  $form["name"]    = trim($_POST["name"] ?? "");
  $form["email"]   = trim($_POST["email"] ?? "");
  $form["phone"]   = trim($_POST["phone"] ?? "");
  $form["service"] = trim($_POST["service"] ?? "");
  $form["message"] = trim($_POST["message"] ?? "");

  // Validate
  if (mb_strlen($form["name"]) < 2) $errors[] = "Name is required (min 2 characters).";
  if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if ($form["phone"] !== "" && !preg_match('/^[0-9+\-\s().]{7,25}$/', $form["phone"])) $errors[] = "Phone format looks invalid.";
  if (mb_strlen($form["message"]) < 15) $errors[] = "Message is required (min 15 characters).";

  if (!$errors) {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $ua = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";

    $body =
      "New inquiry — {$siteName}\n\n" .
      "Name: {$form["name"]}\n" .
      "Email: {$form["email"]}\n" .
      "Phone: " . ($form["phone"] ?: "-") . "\n" .
      "Service: " . ($form["service"] ?: "-") . "\n\n" .
      "Message:\n{$form["message"]}\n\n" .
      "----\nIP: {$ip}\nUser-Agent: {$ua}\n";

    // For better deliverability, set From: to an email on your domain (once you have it).
    // For now, we keep From as your destination (common shared-hosting requirement).
    $headers = [
      "MIME-Version: 1.0",
      "Content-Type: text/plain; charset=UTF-8",
      "From: {$siteName} <{$emailTo}>",
      "Reply-To: {$form["name"]} <{$form["email"]}>"
    ];

    $sent = @mail($emailTo, "[Website] Roofing Inquiry", $body, implode("\r\n", $headers));

    if ($sent) {
      $success = true;
      $form = ["name"=>"","email"=>"","phone"=>"","service"=>"","message"=>""];
    } else {
      // If mail() is not configured, log the message so nothing is lost.
      $logLine = "[" . date("Y-m-d H:i:s") . "] " . str_replace("\n", " | ", $body) . "\n\n";
      @file_put_contents(__DIR__ . "/contact_submissions.log", $logLine, FILE_APPEND);
      $errors[] = "Message saved, but email delivery is not configured on this server. Please call us.";
    }
  }
}

// -------------------------
// SEO
// -------------------------
$pageTitle = "{$siteName} | Roofing Services in Houston, TX";
$description = "Alex Roofing LLC provides roof inspections, repairs, replacements, and storm damage support in Houston, TX. Fast scheduling, clear estimates, and quality workmanship.";
$keywords = "roofing houston, roof repair houston, roof replacement houston, storm damage roof, roof inspection, leak repair";
?>
<!doctype html>
<html lang="en">
<head>
     <!-- Histats.com  START  (aync)-->
<script type="text/javascript">var _Hasync= _Hasync|| [];
_Hasync.push(['Histats.start', '1,5004001,4,0,0,0,00010000']);
_Hasync.push(['Histats.fasi', '1']);
_Hasync.push(['Histats.track_hits', '']);
(function() {
var hs = document.createElement('script'); hs.type = 'text/javascript'; hs.async = true;
hs.src = ('//s10.histats.com/js15_as.js');
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(hs);
})();</script>
<noscript><a href="/" target="_blank"><img  src="//sstatic1.histats.com/0.gif?5004001&101" alt="stat counter" border="0"></a></noscript>
<!-- Histats.com  END  -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />

  <title><?php echo h($pageTitle); ?></title>
  <meta name="description" content="<?php echo h($description); ?>" />
  <meta name="keywords" content="<?php echo h($keywords); ?>" />
  <meta name="author" content="<?php echo h($siteName); ?>" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
  <link rel="canonical" href="<?php echo h($canonicalUrl); ?>" />

  <meta name="theme-color" content="#0b1220" />

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?php echo h($siteName); ?>" />
  <meta property="og:title" content="<?php echo h($pageTitle); ?>" />
  <meta property="og:description" content="<?php echo h($description); ?>" />
  <meta property="og:url" content="<?php echo h($canonicalUrl); ?>" />

  <!-- LocalBusiness schema -->
  <script type="application/ld+json">
  <?php
    $schema = [
      "@context" => "https://schema.org",
      "@type" => "LocalBusiness",
      "name" => $siteName,
      "description" => $description,
      "telephone" => $phones[0],
      "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "P.O. Box 272427",
        "addressLocality" => "Houston",
        "addressRegion" => "TX",
        "postalCode" => "77277",
        "addressCountry" => "US"
      ],
      "areaServed" => "Houston, TX",
      "url" => $canonicalUrl
    ];
    echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  ?>
  </script>

  <style>
    :root{
      --bg:#070b14;
      --panel:#0b1220;
      --panel2:#0f1a2f;
      --text:#e9f0ff;
      --muted:#a9b7d6;
      --line:rgba(233,240,255,.12);
      --accent:#5ad1ff;
      --accent2:#2a7cff;
      --warn:#ffcc66;
      --ok:#7dffb2;
      --danger:#ff7b7b;
      --shadow:0 18px 50px rgba(0,0,0,.55);
      --radius:18px;
      --max:1120px;
      --sans: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{
      margin:0;
      font-family:var(--sans);
      color:var(--text);
      background:
        radial-gradient(900px 520px at 20% -10%, rgba(90,209,255,.14), transparent 55%),
        radial-gradient(800px 520px at 90% 10%, rgba(42,124,255,.12), transparent 55%),
        var(--bg);
      line-height:1.65;
    }
    a{color:inherit;text-decoration:none}
    .container{max-width:var(--max); margin:0 auto; padding:0 22px}

    .skip{
      position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden
    }
    .skip:focus{
      left:22px; top:18px; width:auto; height:auto;
      padding:10px 12px; background:var(--panel); border:1px solid var(--line);
      border-radius:12px; z-index:9999;
    }

    header{
      position:sticky; top:0; z-index:999;
      background: rgba(7,11,20,.78);
      backdrop-filter: blur(10px);
      border-bottom:1px solid var(--line);
    }
    .topbar{
      display:flex; align-items:center; justify-content:space-between; gap:14px;
      padding:14px 0;
    }
    .brand{display:flex; flex-direction:column; gap:2px}
    .brand strong{font-size:18px; letter-spacing:.3px}
    .brand span{font-size:12px; color:var(--muted); letter-spacing:.12em; text-transform:uppercase}

    nav ul{list-style:none;margin:0;padding:0;display:flex;gap:10px;align-items:center}
    nav a{
      font-size:13px; color:var(--muted);
      padding:10px 12px; border-radius:999px;
      border:1px solid transparent;
      transition:all .15s ease;
      white-space:nowrap;
    }
    nav a:hover{color:var(--text); border-color:rgba(90,209,255,.25); background:rgba(90,209,255,.06)}
    nav a.active{color:var(--text); border-color:rgba(90,209,255,.45); background:rgba(90,209,255,.10)}

    .btn{
      display:inline-flex; align-items:center; justify-content:center;
      padding:10px 14px; border-radius:999px;
      border:1px solid rgba(90,209,255,.42);
      background: linear-gradient(180deg, rgba(90,209,255,.18), rgba(42,124,255,.10));
      color:var(--text);
      font-size:13px;
      cursor:pointer;
      transition:all .15s ease;
      white-space:nowrap;
    }
    .btn:hover{background: linear-gradient(180deg, rgba(90,209,255,.24), rgba(42,124,255,.12))}
    .btn.secondary{
      border-color:rgba(233,240,255,.16);
      background:rgba(255,255,255,.03);
      color:var(--text);
    }
    .menuBtn{
      display:none;
      border:1px solid rgba(233,240,255,.16);
      background:rgba(255,255,255,.03);
      padding:10px 12px; border-radius:999px;
      font-size:13px; color:var(--text);
      cursor:pointer;
    }

    .mobileNav{
      display:none;
      border-top:1px solid var(--line);
      padding:10px 0 14px;
    }
    .mobileNav a{
      display:block;
      padding:10px 12px;
      border-radius:12px;
      color:var(--muted);
      border:1px solid transparent;
    }
    .mobileNav a:hover{color:var(--text); border-color:rgba(90,209,255,.25); background:rgba(90,209,255,.06)}
    .mobileNav a.active{color:var(--text); border-color:rgba(90,209,255,.45); background:rgba(90,209,255,.10)}

    section{padding:72px 0}
    .panel{
      background: linear-gradient(180deg, rgba(15,26,47,.90), rgba(11,18,32,.90));
      border:1px solid var(--line);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .pad{padding:18px}

    .hero{padding:0}
    .heroInner{
      padding:82px 0 56px;
      border-bottom:1px solid var(--line);
      background:
        radial-gradient(900px 520px at 15% 10%, rgba(90,209,255,.18), transparent 55%),
        radial-gradient(800px 520px at 85% 20%, rgba(42,124,255,.16), transparent 55%);
    }
    .heroGrid{
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap:18px;
      align-items:end;
    }
    .kicker{
      font-size:12px;
      letter-spacing:.18em;
      text-transform:uppercase;
      color:rgba(233,240,255,.82);
    }
    h1{
      margin:10px 0 12px;
      font-size:46px;
      line-height:1.05;
      letter-spacing:-.02em;
    }
    .lead{
      margin:0 0 16px;
      color:rgba(233,240,255,.84);
      font-size:16px;
      max-width:68ch;
    }
    .pillRow{display:flex; flex-wrap:wrap; gap:10px; margin-top:12px}
    .pill{
      border:1px solid rgba(90,209,255,.22);
      background:rgba(90,209,255,.07);
      color:rgba(233,240,255,.88);
      padding:8px 10px;
      border-radius:999px;
      font-size:12px;
      white-space:nowrap;
    }

    .feature{
      display:flex; gap:14px; align-items:flex-start;
      padding:16px;
      border-radius:var(--radius);
      border:1px solid var(--line);
      background: rgba(255,255,255,.02);
    }
    .dot{
      min-width:10px; height:10px; margin-top:8px;
      border-radius:999px;
      background:var(--accent);
      box-shadow:0 0 0 3px rgba(90,209,255,.14);
    }
    .feature strong{display:block; font-size:16px; margin-bottom:6px}
    .feature p{margin:0; color:var(--muted); font-size:14px}

    .grid2{display:grid; grid-template-columns: 1fr 1fr; gap:16px}
    .grid3{display:grid; grid-template-columns: repeat(3, 1fr); gap:16px}

    .sectionTitle{display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:18px}
    .sectionTitle h2{margin:0; font-size:28px; letter-spacing:-.01em}
    .sectionTitle p{margin:0; color:var(--muted); font-size:14px; max-width:620px}

    .mini{margin:0; color:var(--muted); font-size:13px}

    form{display:grid; gap:12px}
    .row2{display:grid; grid-template-columns: 1fr 1fr; gap:12px}
    label{display:block; margin:0 0 6px; font-size:12px; color:rgba(233,240,255,.78); letter-spacing:.06em; text-transform:uppercase}
    input, textarea, select{
      width:100%;
      padding:12px 12px;
      border-radius:14px;
      border:1px solid rgba(233,240,255,.14);
      background: rgba(7,11,20,.35);
      color:var(--text);
      outline:none;
      font-size:14px;
    }
    textarea{min-height:140px; resize:vertical}
    input:focus, textarea:focus, select:focus{border-color: rgba(90,209,255,.60); box-shadow: 0 0 0 3px rgba(90,209,255,.14)}

    .notice{
      border-radius:16px;
      padding:12px 12px;
      border:1px solid var(--line);
      background: rgba(255,255,255,.02);
      font-size:14px;
      margin-bottom:12px;
    }
    .notice.ok{border-color: rgba(125,255,178,.25); background: rgba(125,255,178,.08)}
    .notice.err{border-color: rgba(255,123,123,.25); background: rgba(255,123,123,.08)}
    .notice ul{margin:8px 0 0 18px}

    footer{
      border-top:1px solid var(--line);
      background: rgba(7,11,20,.82);
      padding:26px 0;
    }
    .footerGrid{display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap}
    .footLinks{display:flex; gap:10px; flex-wrap:wrap}
    .footLinks a{
      color:var(--muted);
      padding:8px 10px;
      border-radius:999px;
      border:1px solid transparent;
    }
    .footLinks a:hover{color:var(--text); border-color:rgba(90,209,255,.25); background:rgba(90,209,255,.06)}

    @media (max-width: 980px){
      .heroGrid{grid-template-columns: 1fr}
      nav ul{display:none}
      .menuBtn{display:inline-flex}
      .grid3{grid-template-columns: 1fr}
      .grid2{grid-template-columns: 1fr}
      .row2{grid-template-columns: 1fr}
      h1{font-size:38px}
    }
  </style>
</head>

<body>
  <a class="skip" href="#main">Skip to content</a>

  <header>
    <div class="container">
      <div class="topbar">
        <div class="brand">
          <strong><?php echo h($siteName); ?></strong>
          <span>Roofing · Houston, TX</span>
        </div>

        <nav aria-label="Primary navigation">
          <ul id="desktopNav">
            <li><a href="#home" data-link="home">Home</a></li>
            <li><a href="#services" data-link="services">Services</a></li>
            <li><a href="#process" data-link="process">Process</a></li>
            <li><a href="#faq" data-link="faq">FAQ</a></li>
            <li><a href="#contact" data-link="contact">Contact</a></li>
          </ul>
        </nav>

        <div style="display:flex; gap:10px; align-items:center;">
          <a class="btn" href="#contact">Get a Quote</a>
          <a class="btn secondary" href="tel:<?php echo h(tel_clean($phones[0])); ?>">Call</a>
          <button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="mobileNav">Menu</button>
        </div>
      </div>

      <div class="mobileNav" id="mobileNav" aria-label="Mobile navigation">
        <a href="#home" data-link="home">Home</a>
        <a href="#services" data-link="services">Services</a>
        <a href="#process" data-link="process">Process</a>
        <a href="#faq" data-link="faq">FAQ</a>
        <a href="#contact" data-link="contact">Contact</a>
      </div>
    </div>
  </header>

  <main id="main">

    <!-- HERO -->
    <section class="hero" id="home" aria-label="Hero">
      <div class="heroInner">
        <div class="container">
          <div class="heroGrid">
            <div>
              <div class="kicker">Inspections · Repairs · Replacements · Storm support</div>
              <h1>Clear estimates. Clean work. Roofing done right.</h1>
              <p class="lead">
                <?php echo h($siteName); ?> helps homeowners and small businesses protect their property with practical roofing solutions,
                honest recommendations, and workmanship you can trust.
              </p>

              <div class="pillRow" aria-label="Highlights">
                <span class="pill">Leak diagnostics</span>
                <span class="pill">Shingle & flat roofing</span>
                <span class="pill">Storm damage inspection</span>
                <span class="pill">Preventive maintenance</span>
              </div>

              <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
                <a class="btn" href="#contact">Request a Quote</a>
                <a class="btn secondary" href="#services">View Services</a>
              </div>

              <p class="mini" style="margin-top:14px;">
                Serving Houston and surrounding areas. Call:
                <a href="tel:<?php echo h(tel_clean($phones[0])); ?>" style="text-decoration:underline; text-underline-offset:3px;"><?php echo h($phones[0]); ?></a>
                <?php if (!empty($phones[1])): ?>
                  · <a href="tel:<?php echo h(tel_clean($phones[1])); ?>" style="text-decoration:underline; text-underline-offset:3px;"><?php echo h($phones[1]); ?></a>
                <?php endif; ?>
              </p>
            </div>

            <aside class="panel">
              <div class="pad">
                <strong style="display:block; font-size:18px; margin-bottom:10px;">What you get</strong>

                <div class="feature">
                  <div class="dot"></div>
                  <div>
                    <strong>Fast inspection scheduling</strong>
                    <p>We confirm the visit window and show up ready to diagnose the issue.</p>
                  </div>
                </div>

                <div style="height:10px;"></div>

                <div class="feature">
                  <div class="dot"></div>
                  <div>
                    <strong>Clear written estimate</strong>
                    <p>Scope, materials, and timeline explained in plain language.</p>
                  </div>
                </div>

                <div style="height:10px;"></div>

                <div class="feature">
                  <div class="dot"></div>
                  <div>
                    <strong>Clean jobsite standards</strong>
                    <p>Protection, debris control, and a final walkthrough.</p>
                  </div>
                </div>

              </div>
            </aside>

          </div>
        </div>
      </div>
    </section>

    <!-- SERVICES -->
    <section id="services" aria-label="Services">
      <div class="container">
        <div class="sectionTitle">
          <h2>Services</h2>
          <p>From small leaks to full replacements — we focus on durable fixes and straightforward options.</p>
        </div>

        <div class="grid3">
          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Roof inspections</strong>
            <p class="mini" style="margin-top:8px;">Leak source checks, flashing evaluation, and storm damage documentation.</p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Repairs & leak mitigation</strong>
            <p class="mini" style="margin-top:8px;">Targeted repairs for shingles, vents, valleys, penetrations, and flashing.</p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Roof replacement</strong>
            <p class="mini" style="margin-top:8px;">Removal, underlayment, new install, cleanup, and final quality check.</p>
          </div></div>
        </div>

        <div style="height:16px;"></div>

        <div class="grid2">
          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Storm damage support</strong>
            <p class="mini" style="margin-top:8px;">
              We document visible impacts, identify vulnerabilities, and provide a clear scope of recommended work.
            </p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:18px;">Preventive maintenance</strong>
            <p class="mini" style="margin-top:8px;">
              Seasonal checkups and small corrections that help extend roof life and prevent surprises.
            </p>
          </div></div>
        </div>
      </div>
    </section>

    <!-- PROCESS -->
    <section id="process" aria-label="Process">
      <div class="container">
        <div class="sectionTitle">
          <h2>How it works</h2>
          <p>A simple process that keeps you informed at every step.</p>
        </div>

        <div class="panel">
          <div class="pad">
            <div class="grid3">
              <div class="feature">
                <div class="dot"></div>
                <div>
                  <strong>1) Inspect</strong>
                  <p>We assess the roof, identify issues, and photograph key findings.</p>
                </div>
              </div>

              <div class="feature">
                <div class="dot"></div>
                <div>
                  <strong>2) Estimate</strong>
                  <p>You get a clear scope, material options, and a realistic timeline.</p>
                </div>
              </div>

              <div class="feature">
                <div class="dot"></div>
                <div>
                  <strong>3) Complete</strong>
                  <p>We execute the work, clean up thoroughly, and do a final walkthrough.</p>
                </div>
              </div>
            </div>

            <div style="height:14px;"></div>

            <p class="mini" style="margin:0;">
              Tip: If you have an active leak, mention it in the form — we’ll prioritize scheduling.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" aria-label="FAQ">
      <div class="container">
        <div class="sectionTitle">
          <h2>FAQ</h2>
          <p>Quick answers to common roofing questions.</p>
        </div>

        <div class="grid2">
          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:16px;">How do I know if I need repair or replacement?</strong>
            <p class="mini" style="margin-top:8px;">
              Many leaks can be repaired if the roof structure is sound. We’ll explain what we see and why we recommend a specific option.
            </p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:16px;">Do you provide written estimates?</strong>
            <p class="mini" style="margin-top:8px;">
              Yes — scope, materials, and timeline in a clear format you can keep for your records.
            </p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:16px;">What should I do during an active leak?</strong>
            <p class="mini" style="margin-top:8px;">
              Place a container under the drip, protect valuables, and contact us. If safe, note where water appears (room + ceiling area).
            </p>
          </div></div>

          <div class="panel"><div class="pad">
            <strong style="display:block; font-size:16px;">What areas do you serve?</strong>
            <p class="mini" style="margin-top:8px;">
              Houston and surrounding areas. If you’re outside Houston, send your ZIP code and we’ll confirm.
            </p>
          </div></div>
        </div>
      </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" aria-label="Contact">
      <div class="container">
        <div class="sectionTitle">
          <h2>Contact</h2>
          <p>Tell us what’s going on. We’ll reply with next steps and availability.</p>
        </div>

        <div class="grid2">
          <div class="panel">
            <div class="pad">

              <?php if ($success): ?>
                <div class="notice ok" role="status" aria-live="polite">
                  Message received. We’ll respond shortly.
                </div>
              <?php endif; ?>

              <?php if ($errors): ?>
                <div class="notice err" role="alert">
                  Please fix the following:
                  <ul>
                    <?php foreach ($errors as $e): ?>
                      <li><?php echo h($e); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <form method="post" action="#contact" novalidate>
                <input type="hidden" name="contact_form" value="1" />
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />

                <!-- Honeypot -->
                <input type="text" name="company_site" value="" tabindex="-1" autocomplete="off"
                       style="position:absolute; left:-9999px; width:1px; height:1px;" />

                <div class="row2">
                  <div>
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="<?php echo h($form["name"]); ?>" required />
                  </div>
                  <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?php echo h($form["email"]); ?>" required />
                  </div>
                </div>

                <div class="row2">
                  <div>
                    <label for="phone">Phone (optional)</label>
                    <input id="phone" name="phone" type="text" value="<?php echo h($form["phone"]); ?>" />
                  </div>
                  <div>
                    <label for="service">Service</label>
                    <select id="service" name="service">
                      <?php
                        $opts = ["","Inspection","Leak repair","Storm damage check","Full replacement","Maintenance"];
                        foreach ($opts as $opt) {
                          $label = $opt === "" ? "Select…" : $opt;
                          $sel = ($form["service"] === $opt) ? "selected" : "";
                          echo '<option value="' . h($opt) . '" ' . $sel . '>' . h($label) . '</option>';
                        }
                      ?>
                    </select>
                  </div>
                </div>

                <div>
                  <label for="message">Details</label>
                  <textarea id="message" name="message" required><?php echo h($form["message"]); ?></textarea>
                  <p class="mini">Include: address/ZIP, issue description, when it started, and whether it’s actively leaking.</p>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:2px;">
                  <button class="btn" type="submit" id="submitBtn">Send message</button>
                  <a class="btn secondary" href="tel:<?php echo h(tel_clean($phones[0])); ?>">Call <?php echo h($phones[0]); ?></a>
                </div>
              </form>
            </div>
          </div>

          <div class="panel">
            <div class="pad">
              <strong style="display:block; font-size:18px;">Business info</strong>

              <p class="mini" style="margin-top:10px;">
                <strong style="color:rgba(233,240,255,.92);">Address:</strong><br>
                <?php echo h($address); ?>
              </p>

              <p class="mini" style="margin-top:10px;">
                <strong style="color:rgba(233,240,255,.92);">Phone:</strong><br>
                <a href="tel:<?php echo h(tel_clean($phones[0])); ?>" style="text-decoration:underline; text-underline-offset:3px;">
                  <?php echo h($phones[0]); ?>
                </a>
                <?php if (!empty($phones[1])): ?>
                  <br>
                  <a href="tel:<?php echo h(tel_clean($phones[1])); ?>" style="text-decoration:underline; text-underline-offset:3px;">
                    <?php echo h($phones[1]); ?>
                  </a>
                <?php endif; ?>
              </p>

              <p class="mini" style="margin-top:10px;">
                <strong style="color:rgba(233,240,255,.92);">Email:</strong><br>
                <a href="mailto:<?php echo h($emailTo); ?>" style="text-decoration:underline; text-underline-offset:3px;">
                  <?php echo h($emailTo); ?>
                </a>
              </p>

              <div style="height:12px;"></div>

              <div class="feature">
                <div class="dot"></div>
                <div>
                  <strong>Emergency note</strong>
                  <p>If water is entering fast, call first. We’ll advise immediate mitigation steps.</p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer>
    <div class="container">
      <div class="footerGrid">
        <div>
          <strong style="font-size:18px;"><?php echo h($siteName); ?></strong>
          <div class="mini" style="margin-top:8px;">
            <?php echo h($tagline); ?><br>
            <?php echo h($address); ?>
          </div>
        </div>

        <div class="footLinks" aria-label="Footer navigation">
          <a href="#home" data-link="home">Home</a>
          <a href="#services" data-link="services">Services</a>
          <a href="#process" data-link="process">Process</a>
          <a href="#faq" data-link="faq">FAQ</a>
          <a href="#contact" data-link="contact">Contact</a>
          <a href="#home" id="backToTop">Back to top</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    (function () {
      // Mobile menu toggle
      const menuBtn = document.getElementById("menuBtn");
      const mobileNav = document.getElementById("mobileNav");

      if (menuBtn && mobileNav) {
        menuBtn.addEventListener("click", () => {
          const isOpen = mobileNav.style.display === "block";
          mobileNav.style.display = isOpen ? "none" : "block";
          menuBtn.setAttribute("aria-expanded", String(!isOpen));
        });

        mobileNav.querySelectorAll('a[href^="#"]').forEach(a => {
          a.addEventListener("click", () => {
            mobileNav.style.display = "none";
            menuBtn.setAttribute("aria-expanded", "false");
          });
        });
      }

      // Active nav highlighting
      const navLinks = Array.from(document.querySelectorAll("[data-link]"));
      const sections = ["home","services","process","faq","contact"]
        .map(id => document.getElementById(id))
        .filter(Boolean);

      function setActive(id) {
        navLinks.forEach(a => a.classList.toggle("active", a.getAttribute("data-link") === id));
      }

      if ("IntersectionObserver" in window) {
        const obs = new IntersectionObserver((entries) => {
          const visible = entries
            .filter(e => e.isIntersecting)
            .sort((a,b) => b.intersectionRatio - a.intersectionRatio)[0];
          if (visible && visible.target && visible.target.id) setActive(visible.target.id);
        }, { threshold: [0.25, 0.5, 0.75] });

        sections.forEach(s => obs.observe(s));
      } else {
        window.addEventListener("scroll", () => {
          let current = "home";
          const y = window.scrollY + 140;
          sections.forEach(s => { if (s.offsetTop <= y) current = s.id; });
          setActive(current);
        });
      }
      setActive(location.hash.replace("#","") || "home");

      // Back to top
      const backToTop = document.getElementById("backToTop");
      if (backToTop) {
        backToTop.addEventListener("click", (e) => {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: "smooth" });
          history.replaceState(null, "", "#home");
        });
      }

      // Prevent double submit (UX only; server still validates)
      const form = document.querySelector('form[action="#contact"]');
      const submitBtn = document.getElementById("submitBtn");
      if (form && submitBtn) {
        form.addEventListener("submit", () => {
          submitBtn.disabled = true;
          submitBtn.textContent = "Sending...";
          setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = "Send message";
          }, 8000);
        });
      }
    })();
  </script>
</body>
</html>

