@php $notificationConfig = \App\Support\NotificationFixtures::configuration(); @endphp
@if($notificationConfig)
@once
<script type="application/json" data-notification-config>@json($notificationConfig)</script>
@endonce
@endif
