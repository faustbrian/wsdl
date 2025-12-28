<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Policy\Policy;
use Cline\WsdlBuilder\WsExtensions\Security\Enums\SecurityTokenInclusion;
use Cline\WsdlBuilder\WsExtensions\Trust\Claims;
use Cline\WsdlBuilder\WsExtensions\Trust\Enums\KeyType;
use Cline\WsdlBuilder\WsExtensions\Trust\Enums\TokenType;
use Cline\WsdlBuilder\WsExtensions\Trust\IssuedToken;
use Cline\WsdlBuilder\WsExtensions\Trust\RequestSecurityToken;
use Cline\WsdlBuilder\WsExtensions\Trust\SecureConversation;
use Cline\WsdlBuilder\WsExtensions\Trust\TrustPolicy;

describe('WS-Trust', function (): void {
    describe('KeyType Enum', function (): void {
        describe('Happy Paths', function (): void {
            test('provides PublicKey key type URI', function (): void {
                // Arrange & Act
                $keyType = KeyType::PublicKey;

                // Assert
                expect($keyType->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-trust/200512/PublicKey');
            });

            test('provides SymmetricKey key type URI', function (): void {
                // Arrange & Act
                $keyType = KeyType::SymmetricKey;

                // Assert
                expect($keyType->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-trust/200512/SymmetricKey');
            });

            test('provides Bearer key type URI', function (): void {
                // Arrange & Act
                $keyType = KeyType::Bearer;

                // Assert
                expect($keyType->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-trust/200512/Bearer');
            });

            test('enum contains all three key types', function (): void {
                // Arrange & Act
                $keyTypes = KeyType::cases();

                // Assert
                expect($keyTypes)->toHaveCount(3)
                    ->and($keyTypes)->toContain(KeyType::PublicKey)
                    ->and($keyTypes)->toContain(KeyType::SymmetricKey)
                    ->and($keyTypes)->toContain(KeyType::Bearer);
            });
        });
    });

    describe('TokenType Enum', function (): void {
        describe('Happy Paths', function (): void {
            test('provides SAML11 token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::SAML11;

                // Assert
                expect($tokenType->value)->toBe('http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#SAMLV1.1');
            });

            test('provides SAML20 token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::SAML20;

                // Assert
                expect($tokenType->value)->toBe('http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#SAMLV2.0');
            });

            test('provides JWT token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::JWT;

                // Assert
                expect($tokenType->value)->toBe('urn:ietf:params:oauth:token-type:jwt');
            });

            test('provides Kerberos token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::Kerberos;

                // Assert
                expect($tokenType->value)->toBe('http://docs.oasis-open.org/wss/oasis-wss-kerberos-token-profile-1.1#GSS_Kerberosv5_AP_REQ');
            });

            test('provides X509 token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::X509;

                // Assert
                expect($tokenType->value)->toBe('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');
            });

            test('provides Username token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::Username;

                // Assert
                expect($tokenType->value)->toBe('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#UsernameToken');
            });

            test('provides Opaque token type URI', function (): void {
                // Arrange & Act
                $tokenType = TokenType::Opaque;

                // Assert
                expect($tokenType->value)->toBe('http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#Opaque');
            });

            test('enum contains all seven token types', function (): void {
                // Arrange & Act
                $tokenTypes = TokenType::cases();

                // Assert
                expect($tokenTypes)->toHaveCount(7);
            });
        });
    });

    describe('Claims', function (): void {
        describe('Happy Paths', function (): void {
            test('creates claims with default dialect URI', function (): void {
                // Arrange & Act
                $claims = new Claims();

                // Assert
                expect($claims->getDialectUri())->toBe('http://docs.oasis-open.org/wsfed/authorization/200706/authclaims')
                    ->and($claims->getClaimTypes())->toBeEmpty();
            });

            test('creates claims with custom dialect URI', function (): void {
                // Arrange & Act
                $claims = new Claims('http://example.com/claims');

                // Assert
                expect($claims->getDialectUri())->toBe('http://example.com/claims');
            });

            test('adds single claim type', function (): void {
                // Arrange
                $claims = new Claims();

                // Act
                $result = $claims->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name');

                // Assert
                expect($result)->toBe($claims)
                    ->and($claims->getClaimTypes())->toHaveCount(1)
                    ->and($claims->getClaimTypes()[0])->toBe('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name');
            });

            test('adds multiple claim types individually', function (): void {
                // Arrange
                $claims = new Claims();

                // Act
                $claims->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name')
                    ->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress');

                // Assert
                expect($claims->getClaimTypes())->toHaveCount(2);
            });

            test('adds multiple claim types via array', function (): void {
                // Arrange
                $claims = new Claims();
                $claimTypes = [
                    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
                    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
                    'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/role',
                ];

                // Act
                $result = $claims->addClaimTypes($claimTypes);

                // Assert
                expect($result)->toBe($claims)
                    ->and($claims->getClaimTypes())->toHaveCount(3);
            });

            test('converts to array representation', function (): void {
                // Arrange
                $claims = new Claims('http://example.com/claims');
                $claims->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name');

                // Act
                $array = $claims->toArray();

                // Assert
                expect($array)->toBe([
                    'dialectUri' => 'http://example.com/claims',
                    'claimTypes' => ['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'],
                ]);
            });
        });
    });

    describe('RequestSecurityToken', function (): void {
        describe('Happy Paths', function (): void {
            test('creates empty RST template', function (): void {
                // Arrange & Act
                $rst = new RequestSecurityToken();

                // Assert
                expect($rst->getTokenType())->toBeNull()
                    ->and($rst->getKeyType())->toBeNull()
                    ->and($rst->getKeySize())->toBeNull()
                    ->and($rst->getClaims())->toBeNull();
            });

            test('sets token type', function (): void {
                // Arrange
                $rst = new RequestSecurityToken();

                // Act
                $result = $rst->tokenType(TokenType::SAML20);

                // Assert
                expect($result)->toBe($rst)
                    ->and($rst->getTokenType())->toBe(TokenType::SAML20);
            });

            test('sets key type', function (): void {
                // Arrange
                $rst = new RequestSecurityToken();

                // Act
                $result = $rst->keyType(KeyType::SymmetricKey);

                // Assert
                expect($result)->toBe($rst)
                    ->and($rst->getKeyType())->toBe(KeyType::SymmetricKey);
            });

            test('sets key size', function (): void {
                // Arrange
                $rst = new RequestSecurityToken();

                // Act
                $result = $rst->keySize(256);

                // Assert
                expect($result)->toBe($rst)
                    ->and($rst->getKeySize())->toBe(256);
            });

            test('sets claims', function (): void {
                // Arrange
                $rst = new RequestSecurityToken();
                $claims = new Claims();
                $claims->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name');

                // Act
                $result = $rst->claims($claims);

                // Assert
                expect($result)->toBe($rst)
                    ->and($rst->getClaims())->toBe($claims);
            });

            test('converts to array with all properties', function (): void {
                // Arrange
                $rst = new RequestSecurityToken();
                $claims = new Claims();
                $claims->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name');

                $rst->tokenType(TokenType::SAML20)
                    ->keyType(KeyType::SymmetricKey)
                    ->keySize(256)
                    ->claims($claims);

                // Act
                $array = $rst->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType', TokenType::SAML20->value)
                    ->and($array)->toHaveKey('keyType', KeyType::SymmetricKey->value)
                    ->and($array)->toHaveKey('keySize', 256)
                    ->and($array)->toHaveKey('claims')
                    ->and($array['claims'])->toBeArray();
            });

            test('converts to array with partial properties', function (): void {
                // Arrange
                $rst = new RequestSecurityToken();
                $rst->tokenType(TokenType::SAML20)
                    ->keyType(KeyType::Bearer);

                // Act
                $array = $rst->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType')
                    ->and($array)->toHaveKey('keyType')
                    ->and($array)->not()->toHaveKey('keySize')
                    ->and($array)->not()->toHaveKey('claims');
            });
        });
    });

    describe('IssuedToken', function (): void {
        describe('Happy Paths', function (): void {
            test('creates issued token assertion', function (): void {
                // Arrange & Act
                $token = new IssuedToken();

                // Assert
                expect($token->getTokenType())->toBe('sp:IssuedToken')
                    ->and($token->getIssuer())->toBeNull()
                    ->and($token->getRequestSecurityTokenTemplate())->toBeNull();
            });

            test('sets issuer endpoint reference', function (): void {
                // Arrange
                $token = new IssuedToken();
                $issuer = new EndpointReference('https://sts.example.com/token');

                // Act
                $result = $token->issuer($issuer);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getIssuer())->toBe($issuer);
            });

            test('sets request security token template', function (): void {
                // Arrange
                $token = new IssuedToken();
                $rst = new RequestSecurityToken();
                $rst->tokenType(TokenType::SAML20)->keyType(KeyType::Bearer);

                // Act
                $result = $token->requestSecurityTokenTemplate($rst);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getRequestSecurityTokenTemplate())->toBe($rst);
            });

            test('sets include token policy', function (): void {
                // Arrange
                $token = new IssuedToken();

                // Act
                $result = $token->includeToken(SecurityTokenInclusion::AlwaysToRecipient);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getIncludeToken())->toBe(SecurityTokenInclusion::AlwaysToRecipient);
            });

            test('converts to array with issuer and RST template', function (): void {
                // Arrange
                $token = new IssuedToken();
                $issuer = new EndpointReference('https://sts.example.com/token');
                $rst = new RequestSecurityToken();
                $rst->tokenType(TokenType::SAML20);

                $token->issuer($issuer)
                    ->requestSecurityTokenTemplate($rst)
                    ->includeToken(SecurityTokenInclusion::AlwaysToRecipient);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType', 'sp:IssuedToken')
                    ->and($array)->toHaveKey('includeToken')
                    ->and($array)->toHaveKey('issuer')
                    ->and($array['issuer'])->toHaveKey('address', 'https://sts.example.com/token')
                    ->and($array)->toHaveKey('requestSecurityTokenTemplate')
                    ->and($array['requestSecurityTokenTemplate'])->toBeArray();
            });

            test('converts to array with issuer having reference parameters', function (): void {
                // Arrange
                $token = new IssuedToken();
                $issuer = new EndpointReference('https://sts.example.com/token');
                $issuer->referenceParameters()->parameter('http://example.com', 'TenantId', 'tenant123');

                $token->issuer($issuer);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array['issuer'])->toHaveKey('referenceParameters');
            });

            test('converts to array with issuer having metadata', function (): void {
                // Arrange
                $token = new IssuedToken();
                $issuer = new EndpointReference('https://sts.example.com/token');
                $issuer->metadata()->add('http://example.com', 'Version', '1.0');

                $token->issuer($issuer);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array['issuer'])->toHaveKey('metadata');
            });
        });
    });

    describe('SecureConversation', function (): void {
        describe('Happy Paths', function (): void {
            test('creates secure conversation token', function (): void {
                // Arrange & Act
                $token = new SecureConversation();

                // Assert
                expect($token->getTokenType())->toBe('sp:SecureConversationToken')
                    ->and($token->getBootstrapPolicy())->toBeNull()
                    ->and($token->getIssuer())->toBeNull();
            });

            test('sets bootstrap policy', function (): void {
                // Arrange
                $token = new SecureConversation();
                $policy = new Policy('BootstrapPolicy');

                // Act
                $result = $token->bootstrapPolicy($policy);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getBootstrapPolicy())->toBe($policy);
            });

            test('sets issuer endpoint reference', function (): void {
                // Arrange
                $token = new SecureConversation();
                $issuer = new EndpointReference('https://sts.example.com/sc');

                // Act
                $result = $token->issuer($issuer);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getIssuer())->toBe($issuer);
            });

            test('sets include token policy', function (): void {
                // Arrange
                $token = new SecureConversation();

                // Act
                $result = $token->includeToken(SecurityTokenInclusion::Once);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getIncludeToken())->toBe(SecurityTokenInclusion::Once);
            });

            test('converts to array with bootstrap policy', function (): void {
                // Arrange
                $token = new SecureConversation();
                $policy = new Policy('BootstrapPolicy');
                $token->bootstrapPolicy($policy);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType', 'sp:SecureConversationToken')
                    ->and($array)->toHaveKey('bootstrapPolicy')
                    ->and($array['bootstrapPolicy'])->toHaveKey('id', 'BootstrapPolicy');
            });

            test('converts to array with issuer', function (): void {
                // Arrange
                $token = new SecureConversation();
                $issuer = new EndpointReference('https://sts.example.com/sc');
                $token->issuer($issuer);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array)->toHaveKey('issuer')
                    ->and($array['issuer'])->toHaveKey('address', 'https://sts.example.com/sc');
            });

            test('converts to array with bootstrap policy and issuer', function (): void {
                // Arrange
                $token = new SecureConversation();
                $policy = new Policy('BootstrapPolicy');
                $issuer = new EndpointReference('https://sts.example.com/sc');

                $token->bootstrapPolicy($policy)
                    ->issuer($issuer)
                    ->includeToken(SecurityTokenInclusion::Once);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType')
                    ->and($array)->toHaveKey('includeToken')
                    ->and($array)->toHaveKey('bootstrapPolicy')
                    ->and($array)->toHaveKey('issuer');
            });

            test('converts to array with issuer having reference parameters', function (): void {
                // Arrange
                $token = new SecureConversation();
                $issuer = new EndpointReference('https://sts.example.com/sc');
                $issuer->referenceParameters()->parameter('http://example.com', 'SessionId', 'session456');

                $token->issuer($issuer);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array['issuer'])->toHaveKey('referenceParameters');
            });
        });
    });

    describe('TrustPolicy Factory', function (): void {
        describe('Happy Paths', function (): void {
            test('creates issued token via factory', function (): void {
                // Arrange & Act
                $token = TrustPolicy::issuedToken();

                // Assert
                expect($token)->toBeInstanceOf(IssuedToken::class)
                    ->and($token->getTokenType())->toBe('sp:IssuedToken');
            });

            test('creates secure conversation token via factory', function (): void {
                // Arrange & Act
                $token = TrustPolicy::secureConversationToken();

                // Assert
                expect($token)->toBeInstanceOf(SecureConversation::class)
                    ->and($token->getTokenType())->toBe('sp:SecureConversationToken');
            });

            test('creates request security token via factory', function (): void {
                // Arrange & Act
                $rst = TrustPolicy::requestSecurityToken();

                // Assert
                expect($rst)->toBeInstanceOf(RequestSecurityToken::class);
            });

            test('creates claims via factory with default dialect', function (): void {
                // Arrange & Act
                $claims = TrustPolicy::claims();

                // Assert
                expect($claims)->toBeInstanceOf(Claims::class)
                    ->and($claims->getDialectUri())->toBe('http://docs.oasis-open.org/wsfed/authorization/200706/authclaims');
            });

            test('creates claims via factory with custom dialect', function (): void {
                // Arrange & Act
                $claims = TrustPolicy::claims('http://example.com/claims');

                // Assert
                expect($claims)->toBeInstanceOf(Claims::class)
                    ->and($claims->getDialectUri())->toBe('http://example.com/claims');
            });

            test('fluent building of issued token with claims', function (): void {
                // Arrange
                $claims = TrustPolicy::claims()
                    ->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name')
                    ->addClaimType('http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress');

                $rst = TrustPolicy::requestSecurityToken()
                    ->tokenType(TokenType::SAML20)
                    ->keyType(KeyType::Bearer)
                    ->claims($claims);

                $issuer = new EndpointReference('https://sts.example.com/token');

                // Act
                $token = TrustPolicy::issuedToken()
                    ->issuer($issuer)
                    ->requestSecurityTokenTemplate($rst)
                    ->includeToken(SecurityTokenInclusion::AlwaysToRecipient);

                $array = $token->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType', 'sp:IssuedToken')
                    ->and($array)->toHaveKey('issuer')
                    ->and($array)->toHaveKey('requestSecurityTokenTemplate')
                    ->and($array['requestSecurityTokenTemplate'])->toHaveKey('claims')
                    ->and($array['requestSecurityTokenTemplate']['claims']['claimTypes'])->toHaveCount(2);
            });

            test('fluent building of secure conversation with bootstrap policy', function (): void {
                // Arrange
                $bootstrapPolicy = new Policy('BootstrapPolicy');
                $bootstrapPolicy->all()->assertion(
                    'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                    'TransportBinding',
                );

                $issuer = new EndpointReference('https://sts.example.com/sc');

                // Act
                $token = TrustPolicy::secureConversationToken()
                    ->bootstrapPolicy($bootstrapPolicy)
                    ->issuer($issuer)
                    ->includeToken(SecurityTokenInclusion::Once);

                $array = $token->toArray();

                // Assert
                expect($array)->toHaveKey('tokenType', 'sp:SecureConversationToken')
                    ->and($array)->toHaveKey('bootstrapPolicy')
                    ->and($array)->toHaveKey('issuer')
                    ->and($array['bootstrapPolicy']['id'])->toBe('BootstrapPolicy');
            });
        });
    });

    describe('Integration with WS-Policy', function (): void {
        describe('Happy Paths', function (): void {
            test('issued token integrates with policy infrastructure', function (): void {
                // Arrange
                $claims = TrustPolicy::claims()
                    ->addClaimTypes([
                        'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
                        'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
                        'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/role',
                    ]);

                $rst = TrustPolicy::requestSecurityToken()
                    ->tokenType(TokenType::SAML20)
                    ->keyType(KeyType::SymmetricKey)
                    ->keySize(256)
                    ->claims($claims);

                $issuer = new EndpointReference('https://sts.example.com/token');
                $issuer->referenceParameters()
                    ->parameter('http://example.com', 'TenantId', 'tenant123');

                $token = TrustPolicy::issuedToken()
                    ->issuer($issuer)
                    ->requestSecurityTokenTemplate($rst)
                    ->includeToken(SecurityTokenInclusion::AlwaysToRecipient);

                // Act
                $config = $token->toArray();

                // Assert
                expect($config)->toBeArray()
                    ->and($config['tokenType'])->toBe('sp:IssuedToken')
                    ->and($config['includeToken'])->toContain('AlwaysToRecipient')
                    ->and($config['issuer']['address'])->toBe('https://sts.example.com/token')
                    ->and($config['issuer'])->toHaveKey('referenceParameters')
                    ->and($config['requestSecurityTokenTemplate']['tokenType'])->toBe(TokenType::SAML20->value)
                    ->and($config['requestSecurityTokenTemplate']['keyType'])->toBe(KeyType::SymmetricKey->value)
                    ->and($config['requestSecurityTokenTemplate']['keySize'])->toBe(256)
                    ->and($config['requestSecurityTokenTemplate']['claims']['claimTypes'])->toHaveCount(3);
            });

            test('secure conversation integrates with policy infrastructure', function (): void {
                // Arrange
                $bootstrapPolicy = new Policy('BootstrapPolicy');
                $all = $bootstrapPolicy->all();
                $all->assertion('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702', 'TransportBinding');
                $all->assertion('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702', 'SignedParts');

                $issuer = new EndpointReference('https://sts.example.com/sc');
                $issuer->metadata()->add('http://example.com', 'Version', '2.0');

                $token = TrustPolicy::secureConversationToken()
                    ->bootstrapPolicy($bootstrapPolicy)
                    ->issuer($issuer)
                    ->includeToken(SecurityTokenInclusion::Once);

                // Act
                $config = $token->toArray();

                // Assert
                expect($config)->toBeArray()
                    ->and($config['tokenType'])->toBe('sp:SecureConversationToken')
                    ->and($config['includeToken'])->toContain('Once')
                    ->and($config['bootstrapPolicy']['id'])->toBe('BootstrapPolicy')
                    ->and($config['issuer']['address'])->toBe('https://sts.example.com/sc')
                    ->and($config['issuer'])->toHaveKey('metadata');
            });
        });
    });
});
