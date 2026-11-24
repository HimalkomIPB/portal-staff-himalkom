@props([
    'title' => config('app.name', 'Portal Staff Himalkom'),
    'preheader' => null,
    'headline' => null,
    'greeting' => null,
    'details' => [],
    'actionText' => null,
    'actionUrl' => null,
    'showFallbackUrl' => true,
])
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>{{ $title }}</title>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,700&display=swap');

        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #F4F7F8;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #2B4248;
        }
        @media only screen and (max-width: 620px) {
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .email-body-cell {
                padding: 32px 24px 28px 24px !important;
            }
            .email-footer-cell {
                padding: 24px 24px 32px 24px !important;
            }
            .email-headline {
                font-size: 22px !important;
            }
            .detail-label {
                width: 34% !important;
            }
            .detail-value {
                padding-left: 16px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F7F8; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #2B4248;">
    @if ($preheader)
        <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #F4F7F8; opacity: 0;">
            {{ $preheader }}
        </div>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #F4F7F8; margin: 0; padding: 0; width: 100%;">
        <tr>
            <td align="center" style="padding: 12px 0 24px 0;">
                <table role="presentation" class="email-container" cellpadding="0" cellspacing="0" border="0" width="600" style="width: 600px; max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-collapse: collapse;">
                    {{-- Header Banner --}}
                    <tr>
                        <td
                            align="center"
                            valign="middle"
                            height="116"
                            bgcolor="#0E293B"
                            background="{{ asset('images/email/header-bg.png') }}"
                            style="height: 116px; background-color: #0E293B; background-image: url('{{ asset('images/email/header-bg.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; border-bottom: 4px solid #AFE1EA; padding: 0 24px; text-align: center;"
                        >
                            <a href="{{ config('app.url') }}" target="_blank" rel="noopener" style="text-decoration: none; display: inline-block;">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto; border-collapse: collapse;">
                                    <tr>
                                        <td valign="middle" style="padding-right: 12px; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 26px; line-height: 1; color: #FFFFFF; white-space: nowrap;">
                                            <span style="font-weight: 700; font-style: italic; letter-spacing: -0.3px;">Himalkom</span><span style="font-weight: 300; letter-spacing: 0.4px;">IPB</span>
                                        </td>
                                        <td valign="middle">
                                            <img
                                                src="{{ asset('images/himalkom_logo.svg') }}"
                                                alt="Logo Himalkom IPB"
                                                width="40"
                                                height="40"
                                                style="display: block; width: 40px; height: 40px; border: 0;"
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </td>
                    </tr>

                    {{-- Main Body --}}
                    <tr>
                        <td class="email-body-cell" style="background-color: #FFFFFF; padding: 40px 36px 36px 36px; text-align: left; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #132C3B;">
                            @if ($headline)
                                <h1 class="email-headline" style="margin: 0 0 24px 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 26px; line-height: 1.3; font-weight: 800; color: #0E293B; letter-spacing: -0.3px;">
                                    {{ $headline }}
                                </h1>
                            @endif

                            @if ($greeting)
                                <p style="margin: 0 0 18px 0; font-size: 16px; line-height: 1.6; font-weight: 600; color: #0E293B;">
                                    {{ $greeting }}
                                </p>
                            @endif

                            <div style="font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
                                {{ $slot }}
                            </div>

                            @if (! empty($details))
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100%; border-collapse: collapse; margin-top: 24px; margin-bottom: 8px;">
                                    @foreach ($details as $detail)
                                        <tr>
                                            <td
                                                class="detail-label"
                                                width="26%"
                                                valign="top"
                                                style="width: 26%; border-top: 1px solid #D6E3E7; padding: 15px 0; font-size: 15px; line-height: 1.5; font-weight: 500; color: #4A5D68; user-select: none; -webkit-user-select: none;"
                                            >{{ trim((string) $detail['label']) }}</td><td
                                                class="detail-value"
                                                valign="top"
                                                style="border-top: 1px solid #D6E3E7; padding: 15px 0 15px 32px; font-size: 15px; line-height: 1.5; color: #0E293B; word-break: break-word; {{ ! empty($detail['bold']) ? 'font-weight: 700;' : 'font-weight: 600;' }} {{ ! empty($detail['mono']) ? "font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; letter-spacing: 0.4px;" : '' }}"
                                            >@if (! empty($detail['html'])){!! $detail['value'] !!}@elseif (! empty($detail['copyable']))<span style="display: inline-block; user-select: all; -webkit-user-select: all;">{{ trim((string) $detail['value']) }}</span>@else{{ trim((string) $detail['value']) }}@endif</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if ($actionText && $actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 28px 0 24px 0; border-collapse: collapse;">
                                    <tr>
                                        <td align="left" bgcolor="#0E293B" style="border-radius: 9999px; background-color: #0E293B;">
                                            <a
                                                href="{{ $actionUrl }}"
                                                target="_blank"
                                                rel="noopener"
                                                style="display: inline-block; padding: 15px 32px; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; line-height: 1.2; color: #FFFFFF; text-decoration: none; border-radius: 9999px; background-color: #0E293B;"
                                            >
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @isset($note)
                                <p style="margin: 0; font-size: 14px; line-height: 1.65; font-weight: 500; color: #4A5D68;">
                                    {{ $note }}
                                </p>
                            @endisset

                            @if ($actionText && $actionUrl && $showFallbackUrl)
                                <p style="margin: 16px 0 0 0; font-size: 12px; line-height: 1.6; font-weight: 500; color: #4A5D68;">
                                    Jika tombol &ldquo;{{ $actionText }}&rdquo; tidak berfungsi, salin dan tempel tautan berikut ke browser kamu:<br />
                                    <a href="{{ $actionUrl }}" target="_blank" rel="noopener" style="color: #0E293B; font-weight: 600; text-decoration: underline; word-break: break-all;">{{ $actionUrl }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td class="email-footer-cell" style="background-color: #FFFFFF; border-top: 1px solid #D6E3E7; padding: 28px 36px 36px 36px; text-align: left; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            <p style="margin: 0 0 4px 0; font-size: 14px; line-height: 1.6; font-weight: 500; color: #4A5D68;">
                                Himpunan Mahasiswa Ilmu Komputer, IPB University
                            </p>
                            <p style="margin: 0 0 18px 0; font-size: 14px; line-height: 1.6; font-weight: 500; color: #4A5D68;">
                                <a href="https://himalkom-ipb.com" target="_blank" rel="noopener" style="color: #0E293B; font-weight: 600; text-decoration: underline;">himalkom-ipb.com</a>
                                &nbsp;&middot;&nbsp;
                                <a href="https://instagram.com/himalkomipb" target="_blank" rel="noopener" style="color: #0E293B; font-weight: 600; text-decoration: underline;">@himalkomipb</a>
                            </p>
                            <p style="margin: 0; font-size: 14px; line-height: 1.6; font-weight: 500; color: #4A5D68;">
                                Email otomatis, tidak perlu dibalas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
