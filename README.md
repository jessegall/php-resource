# php-resource

A library for representing and managing resources in your PHP application. 
It provides a simple, intuitive interface for storing and accessing data, and for managing relations between resources. 
With this library, you can easily create rich, interconnected data models for your application, allowing you to quickly build complex and dynamic features.

## Installation

```
composer require jessegall/resources
```

## Usage

The Resource class can be used to create objects that contain data and support relationships with other Resource objects.

To create a new resource class, extend the Resource class and add any necessary data and methods for your resource.
To access and modify data in the resource, use the `get` and `set` methods:

```php
use JesseGall\Resources\Resource;

class Article extends Resource
{

    public function getTitle(): string 
    {
        return $this->get('title');
    }
    
    public function setTitle(string $title)
    {
        $this->set('title', $title);
    }
    
    public function getBody(): string
    {
        return $this->get('body');
    }
    
    public function setBody(string $body): string
    {
        return $this->string('body', $body);
    }

}
```

To create a new instance of a resource, you can also use the `new` method:

```php
$article = new Article([
    'title' => 'Example Article',
    'body' => 'Lorem ipsum dolor sit amet...'
])

// Or

$article = Article::new([
    'title' => 'Example Article',
    'body' => 'Lorem ipsum dolor sit amet...'
]);
```

To create a collection of resources, use the `collection` method:

```php
$articles = Article::collection([
    [
        'title' => 'Article 1',
        'body' => 'Lorem ipsum dolor sit amet...'
    ],
    [
        'title' => 'Article 2',
        'body' => 'Lorem ipsum dolor sit amet...'
    ]
]);
```

To work with relations between resources, use the `relation` method. This method allows you to map data in a resource to another resource or collection of resources:

```php
class Article extends Resource
{
    public function author(): User
    {
        return $this->relation('author', User::class);
    }

   /**
    * @return ResourceCollection<Comment>
    */
    public function comments(): ResourceCollection
    {
        return $this->relation('comments', Comment::class, true);
    }
}
```

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