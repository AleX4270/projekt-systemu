<?php

declare(strict_types=1);

use App\Enums\OrderStatusType;
use App\Exceptions\Api\OrderQuickAction\OrderAlreadyCompletedException;
use App\Models\Address;
use App\Models\City;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Priority;
use App\Models\Province;
use App\Models\User;
use App\Services\Api\Order\OrderQuickActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function createOrderWithStatus(int $statusId, ?string $dateCompleted = null): Order {
    $country = Country::create(['symbol' => 'PL']);
    $province = Province::create(['country_id' => $country->id, 'name' => 'Mazowieckie']);
    $city = City::create(['province_id' => $province->id, 'name' => 'Warszawa']);

    $address = Address::create([
        'city_id' => $city->id,
        'address' => 'ul. Testowa 1',
        'postal_code' => '00-001',
    ]);

    $client = Client::create([
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'address_id' => $address->id,
    ]);
    
    $priority = Priority::create(['symbol' => 'normal']);
    $creator = User::factory()->create();

    return Order::create([
        'symbol' => 'ORD-TEST',
        'date_deadline' => now()->addWeek(),
        'date_completed' => $dateCompleted,
        'user_creation_id' => $creator->id,
        'priority_id' => $priority->id,
        'client_id' => $client->id,
        'status_id' => $statusId,
    ]);
}

it('marks an active, non-completed order as completed', function () {
    $inProgress = OrderStatus::create(['symbol' => OrderStatusType::IN_PROGRESS->value]);
    $completed = OrderStatus::create(['symbol' => OrderStatusType::COMPLETED->value]);
    $order = createOrderWithStatus($inProgress->id);

    (new OrderQuickActionService())->markAsCompleted($order->id);

    $storedOrder = Order::findOrFail($order->id);

    expect($storedOrder->status_id)->toEqual($completed->id)
        ->and($storedOrder->date_completed)->not->toBeNull();
});

it('throws when the order is already completed and leaves it unchanged', function () {
    $completed = OrderStatus::create(['symbol' => OrderStatusType::COMPLETED->value]);
    $order = createOrderWithStatus($completed->id, now()->subDay()->toDateTimeString());
    $original = Order::findOrFail($order->id);
    $originalStatusId = $original->status_id;
    $originalDateCompleted = $original->date_completed;
    $service = new OrderQuickActionService();

    expect(fn () => $service->markAsCompleted($order->id))
        ->toThrow(OrderAlreadyCompletedException::class);

    $reloaded = Order::findOrFail($order->id);
    
    expect($reloaded->status_id)->toEqual($originalStatusId)
        ->and($reloaded->date_completed)->toEqual($originalDateCompleted);
});
