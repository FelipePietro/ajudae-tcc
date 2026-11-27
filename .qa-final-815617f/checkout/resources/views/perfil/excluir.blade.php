{{-- Shared frontend-only privacy journey; routes use the same view. --}}
@include('privacidade.conta', ['privacy' => \App\Support\PrivacyFixtures::configuration(false), 'institution' => false, 'mode' => 'delete'])
