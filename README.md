# Garlic message bus

This bundle allow microservices communicate to each other with RabbitMQ transport by providing message bus
For correct usage the Bundle must be installed on both services (current and target)

## Configuration and Usage

A couple things are necessary for this bundle to work.  At first, add the Garlic bus bundle to your composer.json and at the second and add config/supervisor.conf file to your supervisor.

### Symfony 4 implementation

```bash
composer require garlic/bus
```

Run

Change and than add config/supervisor.conf file to your supervisor folder.

```bash
cp config/supervisor.conf /etc/supervisor/conf.d/
```

### Now you can use Garlic Bus

#### Common way to use 

If you want to get response from current service you have to use 'request' method, like explained below

```php
$data = $this->get('communicator') // Or you can call by class name. Example: $this->get(GarlicBus:class)
    ->request('targetServiceName') // Type of message. So far you can use ->request() or ->command() methods. Command provide mesage type that not need response. 
    ->get()                        // Set one of REST methods (get, post, put, delete). Bu default set GET 
    ->targetServiceAction(         // CamelCased route where slashes vere changed to upper letter by magic (example: getUser will changed to /get/user)
        array $path = [],          // Path parameters to query (example: ['user' => 1])
        array $query = [],         // Post or Get parameters to the query
        array $headers = []        // Additional headers
    );
    
```
or the same but using direct rout as a method parameter
```php
$data = $this->get('communicator')
    ->request('targetServiceName')
    ->get()
    ->send(
        string $route, // Route to the target service action (example: /user/get)
        array $path = [], 
        array $query = [],
        array $headers = [] 
    );
    
```

#### GraphQL way to get result from service (several services)

**Important:** If you want to use GraphQL wrapper you have to install [garlicservices/graphql-bundle](https://github.com/garlicservices/graphql-bundle) on all the services you requiested in your queries.
To install bundle on application just type in console the command showed below
```bash
composer require garlic/grpahql-bundle
```
##### Easy way to use GraphQl query

Simple example of query data from remote microservice

````php
$graphQLService = $this->get(GraphQLService::class);

$addressQuery = $graphQLService->createQuery('serviceName.QueryName');
$addressQuery
    ->select('id', 'city', 'zipcode')
    ->where('country = Ukraine');

$result = $graphQLService->fetch();
````

##### Querying internal related objects

Example of querying data from related objects
```php
$apartmentQuery = $graphQLService->createQuery('serviceName.QueryName');
$apartmentQuery
    ->select('id', 'buildYear', 'address.id', 'address.city', 'address.country')
    ->where('size = 5');
    
$result = $graphQLService->fetch();    
```

##### Searching on internal related objects

Example of searching data on included objects
```php
$apartmentQuery = $graphQLService->createQuery('serviceName.QueryName');
$apartmentQuery
    ->select('id', 'buildYear', 'address.id', 'address.city', 'address.country')
    ->where('size = 5', 'address.country = Ukraine');
    
$result = $graphQLService->fetch();
```

##### Querying external related objects (stitchOne)

Example of query stitching to one another by using stitchOne() method (stitch result will be included as object)

```php
$graphQLService = $this->get(GraphQLService::class);

$addressQuery = $graphQLService->createQuery('firstServiceName.QueryName');
$addressQuery
    ->select('id', 'city', 'country')
    ->where('country = Ukraine')
;

$apartmentQuery = $graphQLService->createQuery('secondServiceName.QueryName');
$apartmentQuery
    ->select('id', 'size', 'addressId')
    ->where('size = 5')
    ->stitchOne($addressQuery, 'address', 'addressId', 'id')
;

$result = $graphQLService->fetch();

```

##### Querying external related list of objects (stitchMany) 

Example of query stitching to one another by using stitchMany() method (stitch result will be included as list of objects)

```php
$graphQLService = $this->get(GraphQLService::class);

$addressQuery = $graphQLService->createQuery('firstServiceName.QueryName');
$addressQuery
    ->select('id', 'city', 'country')
    ->where('country = Ukraine')
;

$apartmentQuery = $graphQLService->createQuery('secondServiceName.QueryName');
$apartmentQuery
    ->select('id', 'size', 'addressId')
    ->where('size = 5')
    ->stitchMany($addressQuery, 'address', 'addressId', 'id')
;

$result = $graphQLService->fetch();

```

##### Querying stitching by using internaly included objects

Example of stitching queries by fields from internaly included objects

```php
$graphQLService = $this->get(GraphQLService::class);

$addressQuery = $graphQLService->createQuery('firstServiceName.QueryName');
$addressQuery
    ->select('id', 'city', 'country')
    ->where('country = Ukraine')
;

$apartmentQuery = $graphQLService->createQuery('secondServiceName.QueryName');
$apartmentQuery
    ->select('id', 'size', 'address.id', 'address.city', 'address.country')
    ->where('size = 5')
    ->stitchOne($addressQuery, 'fullAddress', 'address.id', 'id')
;

$result = $graphQLService->fetch();

```