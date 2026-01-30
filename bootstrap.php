<?php
session_start();

define('SITE_NAME', 'Roger Clothing');
define('SITE_PHONE', '908-210-3059');
define('SITE_ADDRESS', "P O Box 6105\nBridgewater, NJ 08807\nUSA");
define('BASE_URL', '');

define('ORDER_LOG', __DIR__ . '/orders.log');

$products = [
    'classic-daywear' => [
        'id' => 'classic-daywear',
        'name' => 'Classic Daywear Set',
        'price' => 129.00,
        'image' => 'assets/images/product-flatlay-1.jpg',
        'description' => 'Soft stretch trousers, breathable blouse, and a tailored cardigan for polished comfort.',
        'details' => 'Includes easy-care fabrics, gentle drape, and flattering waistbands for all-day wear.'
    ],
    'evening-elegance' => [
        'id' => 'evening-elegance',
        'name' => 'Evening Elegance',
        'price' => 159.00,
        'image' => 'assets/images/product-flatlay-2.jpg',
        'description' => 'Flowing midi dress with a lightweight shimmer wrap for refined evenings.',
        'details' => 'Designed with a graceful neckline and modest sleeve length for effortless confidence.'
    ],
    'travel-capsule' => [
        'id' => 'travel-capsule',
        'name' => 'Travel Ready Capsule',
        'price' => 189.00,
        'image' => 'assets/images/product-flatlay-3.jpg',
        'description' => 'Wrinkle-resistant separates with mix-and-match versatility.',
        'details' => 'Includes packable layers and breathable textiles ideal for frequent travelers.'
    ],
    'weekend-leisure' => [
        'id' => 'weekend-leisure',
        'name' => 'Weekend Leisure',
        'price' => 99.00,
        'image' => 'assets/images/product-flatlay-1.jpg',
        'description' => 'Relaxed cotton top, pull-on pants, and soft jacket.',
        'details' => 'An easygoing set that keeps you comfortable while looking polished.'
    ],
];

function get_products(): array
{
    global $products;
    return $products;
}

function get_product(string $productId): ?array
{
    $products = get_products();
    return $products[$productId] ?? null;
}

function get_cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(get_cart());
}

function add_to_cart(string $productId, int $quantity): void
{
    if ($quantity < 1) {
        return;
    }

    $cart = get_cart();
    $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
    $_SESSION['cart'] = $cart;
}

function update_cart(array $updates): void
{
    $cart = get_cart();
    foreach ($updates as $productId => $quantity) {
        $quantity = max(0, (int) $quantity);
        if ($quantity === 0) {
            unset($cart[$productId]);
            continue;
        }
        $cart[$productId] = $quantity;
    }
    $_SESSION['cart'] = $cart;
}

function remove_from_cart(string $productId): void
{
    $cart = get_cart();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_items(): array
{
    $cart = get_cart();
    $items = [];
    foreach ($cart as $productId => $quantity) {
        $product = get_product($productId);
        if ($product) {
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $product['price'] * $quantity,
            ];
        }
    }
    return $items;
}

function cart_total(): float
{
    $total = 0;
    foreach (cart_items() as $item) {
        $total += $item['line_total'];
    }
    return $total;
}

function format_currency(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function write_order_log(array $order): void
{
    $entry = json_encode($order, JSON_PRETTY_PRINT);
    file_put_contents(ORDER_LOG, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}
