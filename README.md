# Garlic message bus

This bundle allow microservices communicate to each other with RabbitMQ transport by providing message bus.

For correct usage the Bundle must be installed on both ends of communication (current and target services)

## Installation

Just a couple things are necessary for this bundle works. 

### Add garlic/bus bundle to your composer.json

```bash
composer require garlic/bus
```

### Run processor as a Daemon (add configuration to your supervisor.conf)

```bash
[program:garlic_communication]
command=/var/www/bin/console --env=prod --no-debug enqueue:consume --setup-broker
process_name=%(program_name)s_%(process_num)02d
numprocs=4
autostart=true
autorestart=true
tartsecs=0
user=www-data
redirect_stderr=true
```

Now you can use Garlic Bus

## Usage

### Common way to use 

If you want to get response from current service you have to use 'request' method, like explained below

```php
$data = $this->get('communicator') // Or you can call by class name. Example: $this->get(GarlicBus:class)
    ->request('targetServiceName') // Type of message. So far you can use ->request() or ->command() methods. Command provide mesage type that not need response. 
    ->post()                       // Set one of REST methods (post, put, delete). Bu default set GET 
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
    ->send(
        string $route, // Route to the target service action (example: /user/get)
        array $path = [], 
        array $query = [],
        array $headers = [] 
    );
    
```

#### Making async batch request with parallel processing

```php
$data = $this->get('communicator')
    ->pool(
        'service1', // Target service name
        '/',        // Route to the target service action (example: /user/get)
        [],         // Path parameters to query
        [],         // Post or Get parameters to the query
        []          // Request headers
    )
    ->pool(
        'service1', // Target service name
        '/',        // Route to the target service action (example: /user/get)
        [],         // Path parameters to query
        [],         // Post or Get parameters to the query
        []          // Request headers
        )
    ->fetch();      // Get response from async queries pool
```

#### Working with files
make sure you add variables to .env
```
### host url, will be used by another service to get files from current service
HOST_URL=172.18.1.14

###files will be uploaded to this dir
UPLOAD_DIR = public/upload

###should be same for every service using same bus
SCP_USERNAME=www-data
SCP_PASSWORD=KJLgbJ32PIHDJU4
```
upload files from Request
```
 $handler = $this->get('Garlic\Bus\Service\File\FileHandlerService');
 $handler->handleFiles($_FILES['pictures']);
```
get file from another service
```
 $uploader = $this->get('Garlic\Bus\Service\File\ScpFileUploadService');
 $uploader->getFile(['host_url' => '172.18.0.1','origin_name' => '1.jpg','path' => 'public/upload/fsdljkahb.jpg']);
```