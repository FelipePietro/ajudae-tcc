@props(['participation'])
<section class="panel mt-6" data-certificates="management">
    <h2 class="text-xl font-semibold">Certificados e carga horária</h2>
    <p class="mt-2">Acompanhe a elegibilidade dos participantes. Somente o titular pode gerar seu certificado; as horas vêm das Agendas válidas com presença confirmada.</p>
    <div data-certificate-eligibility aria-live="polite"></div>
    <p data-certificate-error role="alert" hidden>Não foi possível consultar a situação dos certificados.</p>
    <script type="application/json" data-certificate-fixture>@json($participation)</script>
</section>
