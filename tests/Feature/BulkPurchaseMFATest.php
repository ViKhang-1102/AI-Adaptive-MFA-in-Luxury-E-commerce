<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class BulkPurchaseMFATest extends TestCase
{
    use DatabaseTransactions;

    public function test_bulk_purchase_triggers_mfa_and_redirects_to_cod()
    {
        // 1. Create a simulated customer
        $user = User::create([
            'name' => 'MFA Test User',
            'email' => 'mfatest_' . time() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
        
        // Ensure not a verified device
        Session::forget('device_verified');
        Session::forget('user_known_ips');
        
        $this->actingAs($user);

        // 2. Create a seller and expensive product
        $seller = User::where('role', 'seller')->first();
        if (!$seller) {
            $seller = User::create([
                'name' => 'Test Seller',
                'email' => 'seller_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'seller',
            ]);
        }
        
        $category = Category::first() ?? Category::create(['name' => 'Test Cat', 'slug' => 'test-cat']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Super Expensive Diamond',
            'description' => 'Test',
            'price' => 5500.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        // 3. Add to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertStatus(302);

        // 4. Attempt to checkout with COD
        $response = $this->post(route('orders.store'), [
            'shipping_address' => '123 Fake Street',
            'phone' => '1234567890',
            'payment_method' => 'cod',
            'note' => '',
        ]);

        // 5. Assert interception by AI Risk Engine (> $5000, new device flags it = 80+ points => MFA/Block)
        // Wait, 5500 + new device -> 40 + 40 = 80. Above 70 is Block! But wait, we added Trusted Device logic.
        // If amount > 5000 but NOT trusted, it should score 80 and Block/FaceID.
        // Wait, the API says:
        // if data.amount > 5000 and not data.device_is_new: risk_score = 69.0
        // But here device IS new, so score is 80 (-> High -> block -> redirect to FaceID/OTP with block flag?).
        // Actually, our app's `Auth\OTPController` and `RiskAssessmentService` handles 'block' identically for now (requires FaceID/OTP), 
        // Or if it sets 'suggestion' => 'block', we must check how Laravel handles it.
        $response->assertRedirect(route('otp.verify'));
        $this->assertTrue(Session::has('pending_checkout_request'));

        // 6. Simulate entering correct OTP
        Session::put('expected_otp', '123456');

        $otpResponse = $this->post(route('otp.verify.submit'), [
            'otp' => '123456'
        ]);

        // 7. Assert that OTP verification succeeds and redirects to orders.success (or paypal)
        // For COD, it redirects to route('orders.success')
        $otpResponse->assertRedirect(route('orders.success'));
        
        // Check if order was actually created
        $this->assertDatabaseHas('orders', [
            'customer_id' => $user->id,
            'total_amount' => 5500.00,
        ]);
    }
}
