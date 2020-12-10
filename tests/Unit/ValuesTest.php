<?php

namespace SomeoneFamous\AuditableValues\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SomeoneFamous\AuditableValues\Models\TestModel;
use SomeoneFamous\AuditableValues\Tests\TestCase;

class ValuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_new_key_has_null_value()
    {
        $testObject = TestModel::factory()->create();

        $this->assertNull($testObject->getValue());
    }

    /** @test */
    function a_key_can_save_value()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('hello');

        $this->assertDatabaseHas('test_model_values', [
            'test_model_id' => $testObject->id,
            'value' => 'hello'
        ]);
    }

    /** @test */
    function a_key_can_retrieve_current_value()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('hello');

        $this->assertEquals('hello', $testObject->getValue());
    }

    /** @test */
    function a_key_can_retrieve_current_value_via_attribute_accessor()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('hello');

        $this->assertEquals('hello', $testObject->current_value);
    }

    /** @test */
    function a_key_can_retrieve_current_value_when_it_has_historic_values()
    {
        $testObject = TestModel::factory()->create();

        $testObject
            ->setValue('first', Carbon::parse('2020-01-01'))
            ->setValue('second', Carbon::parse('2020-02-01'))
            ->setValue('third', Carbon::parse('2020-03-01'));

        $this->assertEquals('third', $testObject->getValue());
    }

    /** @test */
    function a_key_can_save_value_with_arbitrary_time()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('hello', Carbon::parse('2020-01-01 13:14:15'));

        $this->assertDatabaseHas('test_model_values', [
            'test_model_id' => $testObject->id,
            'value' => 'hello',
            'active_from' => '2020-01-01 13:14:15'
        ]);
    }

    /** @test */
    function a_key_can_update_its_value()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('hello');
        $this->assertEquals('hello', $testObject->getValue());

        $testObject->setValue('goodbye');
        $this->assertEquals('goodbye', $testObject->getValue());

        $this->assertDatabaseHas('test_model_values', [
            'test_model_id' => $testObject->id,
            'value' => 'hello',
        ]);

        $this->assertDatabaseHas('test_model_values', [
            'test_model_id' => $testObject->id,
            'value' => 'goodbye',
        ]);
    }

    /** @test */
    function a_key_can_delete_its_value()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('hello');
        $this->assertEquals('hello', $testObject->getValue());

        $testObject->deleteValue();
        $this->assertNull($testObject->getValue());

        $this->assertDatabaseHas('test_model_values', [
            'test_model_id' => $testObject->id,
            'value' => 'hello',
        ]);
    }

    /** @test */
    function a_key_can_get_value_from_a_specific_time()
    {
        $testObject = TestModel::factory()->create();

        $testObject
            ->setValue('first', Carbon::parse('2020-01-01'))
            ->setValue('second', Carbon::parse('2020-02-01'))
            ->setValue('third', Carbon::parse('2020-03-01'));

        $this->assertEquals('second', $testObject->getValue(Carbon::parse('2020-02-14 12:30:45')));
    }

    /** @test */
    function a_key_can_get_full_history_list()
    {
        $testObject = TestModel::factory()->create();

        $testObject->setValue('first')->setValue('second')->setValue('third');

        $values = $testObject->getHistory();

        $this->assertEquals(3, count($values));

        $this->assertEquals('first', $values[0]->value);
        $this->assertEquals('second', $values[1]->value);
        $this->assertEquals('third', $values[2]->value);
    }

    /** @test */
    function a_key_can_get_history_with_custom_end_time()
    {
        $testObject = TestModel::factory()->create();

        $testObject
            ->setValue('first', Carbon::parse('2020-01-01'))
            ->setValue('second', Carbon::parse('2020-02-01'))
            ->setValue('third', Carbon::parse('2020-03-01'));

        $values = $testObject->getHistory(null, Carbon::parse('2020-02-14 13:44:22'));

        $this->assertEquals(2, count($values));

        $this->assertEquals('first', $values[0]->value);
        $this->assertEquals('second', $values[1]->value);
    }

    /** @test */
    function a_key_can_get_history_with_custom_start_time()
    {
        $testObject = TestModel::factory()->create();

        $testObject
            ->setValue('first', Carbon::parse('2020-01-01'))
            ->setValue('second', Carbon::parse('2020-02-01'))
            ->setValue('third', Carbon::parse('2020-03-01'));

        $values = $testObject->getHistory(Carbon::parse('2020-02-14 13:44:22'), null);

        $this->assertEquals(2, count($values));

        $this->assertEquals('second', $values[0]->value);
        $this->assertEquals('third', $values[1]->value);
    }

    /** @test */
    function a_key_can_get_history_with_custom_start_and_end_times()
    {
        $testObject = TestModel::factory()->create();

        $testObject
            ->setValue('first', Carbon::parse('2020-01-01'))
            ->setValue('second', Carbon::parse('2020-02-01'))
            ->setValue('third', Carbon::parse('2020-03-01'));

        $values = $testObject->getHistory(
            Carbon::parse('2020-02-08 09:00:00'),
            Carbon::parse('2020-02-22 13:24:00')
        );

        $this->assertEquals(1, count($values));

        $this->assertEquals('second', $values[0]->value);
    }
}
