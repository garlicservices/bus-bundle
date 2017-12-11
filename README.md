# Garlic message bus

This bundle allow microservices communicate to each other with RabbitMQ transport

MessageBusBundle is a protocol for messaging among services

## Configuration and Usage

A couple things are necessary for this bundle to work.  At first, add the Garlic bus bundle to your composer.json

### Symfony 4 implementation


```bash
composer require garlic/bus
```

In the app/config/config.yml you will need the following parameters

```yml
enqueue:
    transport:
        default: 'amqp'
        amqp:
            driver: ext
            host: "%env(RABBIT_HOST)%"
            port: "%env(RABBIT_PORT)%"
            user: "%env(RABBIT_USER)%"
            pass: "%env(RABBIT_PASSWORD)%"
            vhost: "%env(RABBIT_DEFAULT_VHOST)%"   
            receive_method: basic_consume         
    client:
        app_name: "%env(SERVICE_NAMESPACE)%"
```

In the app/config/parameters.yml.dist you will need the following parameters
```yml
parameters:
    # RabbitMQ config
    env(RABBIT_HOST): rabbit
    env(RABBIT_PORT): 5672
    env(RABBIT_USER): {Rabbit user}
    env(RABBIT_PASSWORD): {Rabbit pasword}
    env(RABBIT_DEFAULT_VHOST): /

    #App config
    env(SERVICE_NAME): {your service name}
    env(SERVICE_NAMESPACE): {your namemespace}

```
Add to supervisor.conf lines below
```bash
[program:communication]
command={path to your service}/bin/console --env=prod --no-debug --time-limit="now + 5 minutes" --setup-broker enqueue:consume
process_name=%(program_name)s_%(process_num)02d
numprocs=4
autostart=true
autorestart=true
startsecs=0
user=www-data
redirect_stderr=true
```

### Now you can use MessageBusBundle

If you want to get response from current service you have to use 'request' method, like explained below

```php
$data = $this->get('communicator') // Or the way that returns the same result $this->get(MessageBus:class)
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
