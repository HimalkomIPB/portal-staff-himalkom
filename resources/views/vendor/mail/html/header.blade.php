@props(['url'])
<tr>
<td
class="header"
align="center"
valign="middle"
height="116"
bgcolor="#0E293B"
background="{{ asset('images/email/header-bg.png') }}"
style="height: 116px; background-color: #0E293B; background-image: url('{{ asset('images/email/header-bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; border-bottom: 4px solid #AFE1EA; padding: 0 24px; text-align: center;"
>
<a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; text-decoration: none;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto; border-collapse: collapse;">
<tr>
<td valign="middle" style="padding-right: 12px; font-size: 26px; line-height: 1; color: #FFFFFF; white-space: nowrap;">
<span style="font-weight: 700; font-style: italic; letter-spacing: -0.3px;">Himalkom</span><span style="font-weight: 300; letter-spacing: 0.4px;">IPB</span>
</td>
<td valign="middle">
<img src="{{ asset('images/email/himalkom-logo.png') }}" alt="Logo Himalkom IPB" width="40" height="40" style="display: block; width: 40px; height: 40px; border: 0;" />
</td>
</tr>
</table>
</a>
</td>
</tr>
