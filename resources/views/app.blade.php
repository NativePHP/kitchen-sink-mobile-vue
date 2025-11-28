<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body class="font-sans antialiased min-h-screen">
<native:top-bar
    title=""
    show-navigation-icon="{{\Native\Mobile\Facades\System::isAndroid()}}"
>
    <native:top-bar-action
        id="home"
        icon="home"
        label="Home"
        url="{{route('home')}}"
    />

    <native:top-bar-action
        id="docs"
        icon="book-open"
        label="Docs"
        url="https://nativephp.com/docs/mobile/2/getting-started/introduction"
    />
</native:top-bar>
<native:side-nav
    :gestures_enabled="false">
    <native:side-nav-header
        title="NativePHP"
        subtitle="Kitchen Sink App"
        icon="home"
        :show-close-button="true"
        :pinned="true"
    />
    <native:side-nav-group heading="Media"
                           :expanded="request()->routeIs('camera') || request()->routeIs('video') || request()->routeIs('gallery') || request()->routeIs('microphone')">
        <native:side-nav-item active="{{ request()->routeIs('camera') }}" id="camera" icon="camera"
                              url="{{ route('camera') }}" label="Camera" />
        <native:side-nav-item active="{{ request()->routeIs('video') }}" id="video" icon="video"
                              url="{{ route('video') }}" label="Video" badge="New!" badge-color="blue" />
        <native:side-nav-item active="{{ request()->routeIs('gallery') }}" id="gallery" icon="image-plus"
                              url="{{ route('gallery') }}" label="Gallery" />
        <native:side-nav-item active="{{ request()->routeIs('microphone') }}" id="microphone" icon="microphone"
                              url="{{ route('microphone') }}" label="Microphone" badge="New!" badge-color="blue" />
    </native:side-nav-group>
    <native:side-nav-group heading="Dialogs" :expanded="request()->routeIs('alert') || request()->routeIs('toast')">
        <native:side-nav-item active="{{ request()->routeIs('alert') }}" id="alert" icon="bell"
                              url="{{ route('alert') }}" label="Alert" />
        <native:side-nav-item active="{{ request()->routeIs('toast') }}" id="toast" icon="bolt"
                              url="{{ route('toast') }}" label="Toast" />
    </native:side-nav-group>
    <native:side-nav-item active="{{ request()->routeIs('scanner') }}" id="scanner" icon="qrcode"
                          url="{{ route('scanner') }}" label="Scanner" badge="New!" badge-color="blue" />
    <native:side-nav-item active="{{ request()->routeIs('network') }}" id="network" icon="globe"
                          url="{{ route('network') }}" label="Network" badge="New!" badge-color="blue" />
    <native:side-nav-item active="{{ request()->routeIs('biometrics') }}" id="biometrics" icon="finger-print"
                          url="{{ route('biometrics') }}" label="Biometrics" />
    <native:side-nav-item active="{{ request()->routeIs('geolocation') }}" id="geolocation" icon="map"
                          url="{{ route('geolocation') }}" label="Geolocation" />
    <native:side-nav-item active="{{ request()->routeIs('device') }}" id="device" icon="device-phone-mobile"
                          url="{{ route('device') }}" label="Device Info" />
    <native:side-nav-item active="{{ request()->routeIs('haptics') }}" id="haptics" icon="vibrate"
                          url="{{ route('haptics') }}" label="Haptics" />
    <native:side-nav-item active="{{ request()->routeIs('browser') }}" id="browser" icon="globe-alt"
                          url="{{ route('browser') }}" label="Browser" />
    <native:side-nav-item active="{{ request()->routeIs('secure-storage') }}" id="secure-storage" icon="folder-lock"
                          url="{{ route('secure-storage') }}" label="Secure Storage" />
    <native:side-nav-item active="{{ request()->routeIs('push-notifications') }}" id="push-notifications" icon="bell"
                          url="{{ route('push-notifications') }}" label="Push Notifications" />
    <native:horizontal-divider />
    <native:side-nav-group heading="Resources" :expanded="false">
        <native:side-nav-item id="docs" icon="book-open"
                              url="https://nativephp.com/docs/mobile/2/getting-started/introduction" label="Docs" />
        <native:side-nav-item id="learn-more" icon="information-circle" url="https://nativephp.com/mobile"
                              label="Learn More" />
    </native:side-nav-group>
</native:side-nav>
<native:bottom-nav label-visibility="labeled">
    <native:bottom-nav-item
        id="scanner"
        label="Scanner"
        url="{{ route('scanner') }}"
        icon="qrcode"
        :active="request()->routeIs('scanner')"
        news="true"
    />
    <native:bottom-nav-item
        id="video"
        label="Video"
        url="{{ route('video') }}"
        icon="video"
        :active="request()->routeIs('video')"
    />
    <native:bottom-nav-item
        id="microphone"
        label="Microphone"
        url="{{ route('microphone') }}"
        icon="microphone"
        :active="request()->routeIs('microphone')"
    />
    <native:bottom-nav-item
        id="network"
        label="Network"
        url="{{ route('network') }}"
        icon="globe"
        :active="request()->routeIs('network')"
    />
</native:bottom-nav>
@inertia
</body>
</html>
