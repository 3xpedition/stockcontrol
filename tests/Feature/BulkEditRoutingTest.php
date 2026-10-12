<?php

namespace Tests\Feature;

use Tests\TestCase;

class BulkEditRoutingTest extends TestCase
{
    public function test_bulk_form_submission_reaches_selection_validation(): void
    {
        $response = $this->from(route('inventory.bulk-edit', ['q' => 'AGUA']))
            ->post(route('inventory.bulk-update'), [
                '_method' => 'PUT',
                'ncm' => '20098921',
                'cfop' => 5405,
            ]);

        $response->assertRedirect(route('inventory.bulk-edit', ['q' => 'AGUA']));
        $response->assertSessionHasErrors('selected_tokens');
    }
}
