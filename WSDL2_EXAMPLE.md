# WSDL 2.0 Example Usage

## Basic Usage

```php
use Cline\WsdlBuilder\Wsdl2\Wsdl2;
use Cline\WsdlBuilder\Wsdl2\Enums\MessageExchangePattern;

$wsdl = Wsdl2::create('MyService', 'http://example.com/myservice')
    ->documentation('My WSDL 2.0 Service');

// Define complex types
$wsdl->complexType('UserRequest')
    ->sequence()
        ->element('username', 'xsd:string')
        ->element('email', 'xsd:string')
    ->end()
    ->end();

$wsdl->complexType('UserResponse')
    ->sequence()
        ->element('id', 'xsd:int')
        ->element('username', 'xsd:string')
        ->element('created', 'xsd:dateTime')
    ->end()
    ->end();

// Define interface (replaces portType in WSDL 1.1)
$wsdl->interface('UserInterface')
    ->fault('InvalidUserFault', 'tns:InvalidUserError')
    ->operation('CreateUser')
        ->pattern(MessageExchangePattern::InOut->value)
        ->input('tns:UserRequest')
        ->output('tns:UserResponse')
        ->fault('InvalidUserFault')
        ->safe(false)
    ->end()
    ->end();

// Define binding
$wsdl->binding('UserBinding', 'tns:UserInterface')
    ->type(Wsdl2::SOAP_NS)
    ->operation('CreateUser')
        ->soapAction('http://example.com/myservice/CreateUser')
    ->end()
    ->end();

// Define service with endpoint (replaces port in WSDL 1.1)
$wsdl->service('UserService')
    ->interface('tns:UserInterface')
    ->endpoint('UserEndpoint', 'tns:UserBinding', 'http://example.com/api/user')
    ->end()
    ->end();

// Note: Generator not yet implemented
// $xml = $wsdl->build();
```

## Key Differences from WSDL 1.1

1. **Interface instead of PortType**: Use `interface()` method (class name is `Interface_` due to PHP reserved keyword)
2. **Endpoint instead of Port**: Use `endpoint()` method in services
3. **Message Exchange Patterns**: Operations use MEP URIs instead of separate input/output messages
4. **Interface-level Faults**: Faults can be defined at the interface level and referenced in operations
5. **No Messages**: WSDL 2.0 uses element references directly instead of message definitions
6. **Interface Inheritance**: Interfaces can extend other interfaces using `extends()`

## Interface Features

```php
// Interface with inheritance
$wsdl->interface('ExtendedUserInterface')
    ->extends('tns:UserInterface')
    ->operation('UpdateUser')
        ->pattern(MessageExchangePattern::InOut->value)
        ->input('tns:UserRequest')
        ->output('tns:UserResponse')
    ->end()
    ->end();
```

## Message Exchange Patterns

Available patterns:
- `InOut` - Request-response
- `InOnly` - One-way input
- `RobustInOnly` - One-way with fault reporting
- `OutOnly` - One-way output (notification)
- `OutIn` - Reverse request-response
- `OutOptionalIn` - Output with optional input
- `InOptionalOut` - Input with optional output
- `RobustOutOnly` - One-way output with fault reporting

## TODO

- Implement `Wsdl2Generator` class to generate actual XML output
- Add support for HTTP binding (currently only SOAP binding structure is defined)
- Add support for additional WSDL 2.0 features as needed
