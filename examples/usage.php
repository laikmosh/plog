<?php

// Example usage of Plog in a Laravel application

use Illuminate\Support\Facades\Log;
use Laikmosh\Plog\Support\PlogProxy;

// Standard Laravel logging - automatically captures metadata
Log::info('User logged in successfully');
Log::error('Payment processing failed', ['order_id' => 123, 'amount' => 99.99]);

// Using the Plog proxy with tags
PlogProxy::info('Order created', ['order_id' => 456])
    ->tags(['orders', 'checkout']);

PlogProxy::error('Payment gateway timeout', ['gateway' => 'stripe'])
    ->tags(['payment', 'critical', 'stripe']);

// In a controller
class OrderController extends Controller
{
    public function store(Request $request)
    {
        PlogProxy::info('Starting order creation', ['user_id' => auth()->id()])
            ->tags(['orders', 'api']);

        try {
            $order = Order::create($request->validated());

            PlogProxy::info('Order created successfully', ['order_id' => $order->id])
                ->tags(['orders', 'success']);

            return response()->json($order);
        } catch (\Exception $e) {
            PlogProxy::error('Order creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ])->tags(['orders', 'error']);

            throw $e;
        }
    }
}

// In a queued job
class ProcessPaymentJob implements ShouldQueue
{
    public function handle()
    {
        // Request ID is automatically preserved from the original HTTP request
        PlogProxy::info('Processing payment in background job')
            ->tags(['payment', 'queue']);

        // Your job logic here
    }
}

// In an artisan command
class ImportDataCommand extends Command
{
    public function handle()
    {
        PlogProxy::info('Starting data import')
            ->tags(['import', 'cli']);

        $this->importUsers();
        $this->importOrders();

        PlogProxy::info('Data import completed')
            ->tags(['import', 'cli', 'success']);
    }

    private function importUsers()
    {
        PlogProxy::debug('Importing users')
            ->tags(['import', 'users']);
        // Import logic
    }

    private function importOrders()
    {
        PlogProxy::debug('Importing orders')
            ->tags(['import', 'orders']);
        // Import logic
    }
}