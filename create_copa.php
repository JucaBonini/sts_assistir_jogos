<?php
require_once('../../../wp-load.php');
$term_name = 'Copa do Brasil 2026';
$taxonomy = 'campeonato';

$check = get_term_by('name', $term_name, $taxonomy);
if ($check) {
    echo "O termo '{$term_name}' já existe (ID: {$check->term_id}).\n";
} else {
    $result = wp_insert_term($term_name, $taxonomy);
    if (is_wp_error($result)) {
        echo "Erro ao criar: " . $result->get_error_message() . "\n";
    } else {
        echo "Termo '{$term_name}' criado com sucesso (ID: {$result['term_id']}).\n";
    }
}
