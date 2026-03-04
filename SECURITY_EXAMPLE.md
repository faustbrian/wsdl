# WS-Security Policy Assertions - Usage Examples

This document demonstrates how to use the WS-Security policy assertions with the wsdl-builder package.

## Basic Transport Security

```php
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Security\SecurityPolicy;
use Cline\WsdlBuilder\WsExtensions\Security\Enums\AlgorithmSuite;

$wsdl = Wsdl::create('SecureService', 'http://example.com/secure');

// Create a binding with transport security
$wsdl->binding('SecureBinding', 'MyPortType')
    ->policy()
        ->all()
            ->assertion(SecurityPolicy::transportBinding()
                ->includeTimestamp()
                ->algorithmSuite(AlgorithmSuite::Basic256)
                ->transportToken()
                    ->httpsToken()
                    ->requireClientCertificate()
                ->end()
            )
        ->end()
    ->end()
->end();
```

## Username Token Authentication

```php
$wsdl->binding('UserAuthBinding', 'AuthPortType')
    ->policy()
        ->all()
            ->assertion(SecurityPolicy::usernameToken('PasswordText'))
            ->assertion(SecurityPolicy::signedParts(['Body', 'Header']))
        ->end()
    ->end()
->end();
```

## X.509 Certificate Security

```php
$wsdl->binding('CertBinding', 'SecurePortType')
    ->policy()
        ->all()
            ->assertion(SecurityPolicy::x509Token('WssX509V3Token10'))
            ->assertion(SecurityPolicy::signedParts(['Body']))
            ->assertion(SecurityPolicy::encryptedParts(['Body']))
        ->end()
    ->end()
->end();
```

## SAML Token Security

```php
$wsdl->binding('SamlBinding', 'SamlPortType')
    ->policy()
        ->all()
            ->assertion(SecurityPolicy::samlToken('WssSamlV20Token11'))
            ->assertion(SecurityPolicy::signedElements([
                '/s:Envelope/s:Body',
                '/s:Envelope/s:Header/wsa:Action'
            ]))
        ->end()
    ->end()
->end();
```

## Multiple Security Options

```php
$wsdl->binding('FlexibleSecurityBinding', 'FlexiblePortType')
    ->policy()
        ->exactlyOne()
            ->all()
                ->assertion(SecurityPolicy::usernameToken('PasswordDigest'))
                ->assertion(SecurityPolicy::wss11())
            ->end()
            ->all()
                ->assertion(SecurityPolicy::x509Token())
                ->assertion(SecurityPolicy::wss10())
            ->end()
        ->end()
    ->end()
->end();
```

## Available SecurityPolicy Methods

### Binding Types
- `transportBinding()` - HTTPS/TLS transport security
- `symmetricBinding()` - Symmetric key security
- `asymmetricBinding()` - Asymmetric key security (PKI)

### Token Types
- `usernameToken(?string $passwordType)` - Username/password authentication
- `x509Token(?string $tokenType)` - X.509 certificate
- `samlToken(?string $tokenType)` - SAML assertion
- `issuedToken()` - Security token service issued token
- `secureConversationToken()` - WS-SecureConversation token
- `kerberosToken()` - Kerberos ticket
- `spnegoContextToken()` - SPNEGO negotiation

### Protection Assertions
- `signedParts(?array $parts)` - Sign SOAP message parts
- `encryptedParts(?array $parts)` - Encrypt SOAP message parts
- `signedElements(?array $xpaths)` - Sign specific XML elements
- `encryptedElements(?array $xpaths)` - Encrypt specific XML elements

### Version Assertions
- `wss10()` - WS-Security 1.0
- `wss11()` - WS-Security 1.1
- `trust10()` - WS-Trust 1.0
- `trust13()` - WS-Trust 1.3

## Enums

### SecurityTokenInclusion
Specifies when to include the security token:
- `Never` - Never include the token
- `Once` - Include once per message
- `AlwaysToRecipient` - Always include to recipient
- `AlwaysToInitiator` - Always include to initiator
- `Always` - Always include

### AlgorithmSuite
Supported algorithm suites:
- `Basic256`, `Basic192`, `Basic128`
- `TripleDes`
- `Basic256Sha256`, `Basic192Sha256`, `Basic128Sha256`
- `TripleDesSha256`
- `Basic256Rsa15`, `Basic192Rsa15`, `Basic128Rsa15`
- `TripleDesRsa15`
- `Basic256Sha256Rsa15`, `Basic192Sha256Rsa15`, `Basic128Sha256Rsa15`
- `TripleDesSha256Rsa15`

## Layout Policies
When using `TransportBinding`, you can specify layout:
- `Strict` - Elements must appear in specific order
- `Lax` - Flexible ordering
- `LaxTsFirst` - Timestamp must be first
- `LaxTsLast` - Timestamp must be last

Example:
```php
SecurityPolicy::transportBinding()
    ->includeTimestamp()
    ->layout('LaxTsFirst')
    ->algorithmSuite(AlgorithmSuite::Basic256Sha256)
```
