# Garlic message bus

This bundle allow microservices communicate to each other with RabbitMQ transport

MessageBusBundle is a protocol for messaging among services

## Configuration and Usage

A couple things are necessary for this bundle to work.  At first, add the Garlic bus bundle to your composer.json and at the second and add config/supervisor.conf file to your supervisor.

### Symfony 4 implementation

```bash
composer require garlic/bus
```
Change and than add config/supervisor.conf file to your supervisor folder.

```bash
cp config/supervisor.conf /etc/supervisor/conf.d/
```

### Now you can use Garlic Bus

If you want to get response from current service you have to use 'request' method, like explained below

```php
$data = $this->get('communicator') // Or the way that returns the same result is $this->get(MessageBus:class)
    ->request('targetServiceName')
    ->targetServiceAction(   // CamelCased query where slashes ware changed to upper letter by magic
        array $path = [],    // Path parameters to query
        array $query = [],   // Post or Get parameters to the query
        array $headers = []  // Additional headers
    );
    
```
or
```php
$data = $this->get('communicator')
    ->request('targetServiceName')
    ->send(
        string $route, // Route to the service action example: /user/get
        array $path = [], 
        array $query = [],
        array $headers = [] 
    );
    
```

If you need send request by POST (or any other REST method) just add ->post() etc. before call targetServiceAction()
```php
$data = $this->get('communicator')
    ->request('targetServiceName')
    ->post()
    ->targetServiceAction(
        array $path = [],
        array $query = [],
        array $headers = [] 
    );
```

If you want send a command (that never returns an answer) use next solution example
```php
$data = $this->get('communicator')
    ->command('targetServiceName')
    ->targetServiceAction( 
        array $path = [],
        array $query = [],
        array $headers = [] 
    );
```
