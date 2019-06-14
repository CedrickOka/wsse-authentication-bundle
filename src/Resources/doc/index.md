# Getting Started With OkaWSSEAuthenticationBundle

This bundle provides an WSSE authenticator system.

## Prerequisites

The OkaWSSEAuthenticationBundle has the following requirements:

 - PHP 7.2+
 - Symfony 3.4+

## Installation

Installation is a quick (I promise!) 5 step process:

1. Download OkaWSSEAuthenticationBundle
2. Register the Bundle
3. Create your WSSEUser class
4. Configure the Bundle
5. Update your database schema

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require coka/wsse-authentication-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Register the Bundle

**Symfony 3 Version**

Then, register the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Oka\WSSEAuthenticationBundle\OkaWSSEAuthenticationBundle(),
        ];

        // ...
    }

    // ...
}
```

**Symfony 4 Version**

Then, register the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project (Flex did it automatically):

```php
return [
    //...
    Oka\WSSEAuthenticationBundle\OkaWSSEAuthenticationBundle::class => ['all' => true],
]
```

### Step 3: Create your WSSEUser class

The goal of this bundle is to  persist some `WSSEUser` class to a database (MySql). 
Your first job, then, is to create the `WSSEUser` class for you application. 
This class can look and act however you want: add any
properties or methods you find useful. This is *your* `WSSEUser` class.

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. Extend the base `WSSEUser` class (from the `Model` folder)
2. Map the `id` field. It must be protected as it is inherited from the parent class.

**Warning:**

> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.

Your `WSSEUser` class can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeAuthenticationBundle`
and place your `WSSEUser` class in it.

In the following sections, you'll see examples of how your `WSSEUser` class should
look, depending on how you're storing your entities.

**Note:**

> The doc uses a bundle named `AcmeAuthenticationBundle`. If you want to use the same
> name, you need to register it in your kernel. But you can of course place
> your `WSSEUser` class in the bundle you want.

**Warning:**

> If you override the __construct() method in your WSSEUser class, be sure
> to call parent::__construct(), as the base WSSEUser class depends on
> this to initialize some fields.

#### Doctrine ORM WSSEUser class

you must persisting your entity via the Doctrine ORM, then your `WSSEUser` class
should live in the `Entity` namespace of your bundle and look like this to
start:

##### Annotations

```php
<?php
// src/Acme/AuthenticationBundle/Entity/WSSEUser.php

namespace Acme\AuthenticationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\WSSEAuthenticationBundle\Model\WSSEUser as BaseWSSEUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="wsse_user")
 */
class WSSEUser extends BaseWSSEUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        
        // your own logic
    }
}
```

##### YAML

If you use yml to configure Doctrine you must add two files. The Entity and the orm.yml:

```php
<?php
// src/Acme/AuthenticationBundle/Entity/WSSEUser.php

namespace Acme\AuthenticationBundle\Entity;

use Oka\WSSEAuthenticationBundle\Model\WSSEUser as BaseWSSEUser;

/**
 * WSSEUser
 */
class WSSEUser extends BaseWSSEUser
{
	public function __construct()
	{
		parent::__construct();
		
		// your own logic
	}
}
```

```yaml
# src/Acme/AuthenticationBundle/Resources/config/doctrine/WSSEUser.orm.yml
Acme\AuthenticationBundle\Entity\WSSEUser:
    type: entity
    table: wsse_user
    id:
        id:
            type: integer
            generator:
                strategy: AUTO
```

### Step 4: Configure the Bundle

Add the following configuration to your `config/packages/oka_wsse_authentication.yaml`.

``` yaml
# config/packages/oka_wsse_authentication.yaml
oka_wsse_authentication:
    db_driver: orm
    model_manager_name: null
    user_class: Acme\AuthenticationBundle\Entity\WSSEUser
    realm: 'Secure Area'
    nonce:
        lifetime: 300
    enabled_allowed_ips_voter: true # Enables the voter that allows access to requests at only certain ips allocated for the current authenticated client
```

Add the following configuration to your `config/packages/security.yaml`.

``` yaml
# config/packages/security.yaml
security:
# Add `wsse_user_provider` in providers configuration section and using the "oka_wsse" user provider
    providers:
        wsse_user_provider:
            oka_wsse:
                class: Acme\AuthenticationBundle\Entity\WSSEUser

# Add `wsse` in firewalls configuration section
    firewalls:
        wsse:
            request_matcher: oka_wsse_authentication.request_matcher
            stateless: true
            anonymous: true
            provider: wsse_user_provider
            guard:
                authenticators: [oka_wsse_authentication.wsse_authenticator]

# you must define at least one entry in your `access_control`
    access_control:
      - { path: '^/', roles: ROLE_API_USER }

# Define strategy decision like `unanimous` for allows wsse voter has denied access or abstain
# then the wsse voter which control user access by ip is enabled 
    access_decision_manager:
        strategy: unanimous
```

### Step 5: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `WSSEUser` class which you
created in Step 4.

Run the following command.

``` bash
$ php bin/console doctrine:schema:update --force
```

You now can access at the index page `http://acme.com/`!

## How use this?

Now that the bundle is installed

```
curl -i http://acme.com/ -X GET -H 'Authorization: UsernameToken Username="admin", PasswordDigest="53dGT2c83M446zUJfpr9lanpeY0=", Nonce="MTM3OGM2YzJlZDYyNDE5Ng==", Created="2019-03-30T09:52:33Z"'
```
