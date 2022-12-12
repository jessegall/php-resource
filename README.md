# php-resource

```
composer require jessegall/resources
```

## What can it do?

The Resource class can be used to create objects that contain data and support relationships with other Resource objects. 
The Resource class also has methods for creating new Resource objects, creating collections of Resource objects, and defining and accessing relationships with other Resource objects.

## Examples
Here is an example of how you might use this Resource class to represent orders and products in a RESTful API:
```php
namespace App\Resources;

use JesseGall\Resources\Resource;
use JesseGall\Resources\ResourceCollection;

class Order extends Resource
{
    public function __construct(array $data = [])
    {
        // Set the data for the order
        parent::__construct($data);
    }

    /**
     * Map the products of the order to the Product resource type
     *
     * @return ResourceCollection<Product>
     */
    public function products(): ResourceCollection
    {
        return $this->relation('products', Product::class, true);
    }
}

class Product extends Resource
{
    public function __construct(array $data = [])
    {
        // Set the data for the product
        parent::__construct($data);
    }

    /**
     * Get the ID of the product
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->get('id');
    }

    /**
     * Set the ID of the product
     *
     * @param int $id
     * @return Product
     */
    public function setId(int $id): Product
    {
        return $this->set('id', $id);
    }

    /**
     * Get the name of the product
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->get('name');
    }

    /**
     * Set the name of the product
     *
     * @param string $name
     * @return Product
     */
    public function setName(string $name): Product
    {
        return $this->set('name', $name);
    }

    /**
     * Get the price of the product
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->get('price');
    }

    /**
     * Set the price of the product
     *
     * @param float $price
     * @return Product
     */
    public function setPrice(float $price): Product
    {
        return $this->set('price', $price);
    }
}

// Create a new order with data
$order = new Order([
    'id' => 1,
    'customer' => 'John Doe',
    'products' => [
        [
            'id' => 1,
            'name' => 'Shirt',
            'price' => 19.99
        ],
        [
            'id' => 2,
            'name' => 'Pants',
            'price' => 29.99
        ]
    ]
]);

// Get the products of the order as a ResourceCollection of Product resources
$products = $order->products();
```