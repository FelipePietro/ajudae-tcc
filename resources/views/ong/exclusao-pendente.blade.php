{{-- Shared frontend-only privacy journey; routes use the same view. --}}
@include('privacidade.conta', ['privacy' => \App\Support\PrivacyFixtures::configuration(true), 'institution' => true, 'mode' => 'pending'])
