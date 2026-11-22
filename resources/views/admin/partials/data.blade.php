<script type="application/json" data-admin-data>{!! json_encode([
    'page' => $page,
    'records' => $records,
    'statuses' => \App\Support\VisualFourFixtures::STATUSES,
    'tones' => \App\Support\VisualFourFixtures::TONES,
    'events' => array_map(fn ($row) => [
        'id' => $row['id'], 'status' => $row['status'], 'reanalysis' => $row['reanalysis'],
        'category' => $row['category'], 'starts' => $row['starts'], 'ong' => $row['ong'],
    ], $queues['fila-eventos'] ?? []),
    'pending' => \App\Support\VisualFourFixtures::PENDING,
    'counts' => array_map(fn ($rows) => array_column($rows, 'status', 'id'), $queues),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
