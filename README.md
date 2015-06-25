# Propel Model Parser Bundle

The PropelModelParserBundle enables Propel BaseObjects to be parsed into array, which in turn can be converted into JSON for API responses.

Unlike native BaseObject methods like toJSON(), the PropelModelParserBundle can automatically convert BaseObjects as well as their "child objects" to array, as well as add custom properties.

#### Installation:

1. Install PropelModelParserBundle using Composer
2. Register bundle in AppKernel
3.  In your Propel *schema.xml*, Use `kapitanluffy\PropelModelParserBundle\BaseModel` as your baseClass
4. Rebuild Propel models.

#### Usage:
In your controller..
```php
<?php
namespace AppBundle\Controller;

use kapitanluffy\PropelModelParserBundle\PropertyCollection;
use Symfony\Component\HttpFoundation\;

class DefaultController extends Controller
{
    public function indexAction($user_id)
    {
        $user = Model\UserQuery::create()
        ->joinWith('Post')
        ->findOneById($user_id);
        
        $post_count = $user->getPosts()->count();
        
        $properties = new PropertyCollection;
        $properties->addProperty('posts', 'getPosts')
            // parse child object's (post) children (user)
            ->useProperty('posts')
                ->addProperty('poster', 'getUser')
            ->endUse()
            // add custom property
            ->addProperty('post_count', $post_count);
        
        $data = $user->parseObject($properties);
        
        $response = new JsonResponse;
        $response->setData($data);
        return $response;
    }
}
```

Will result into
``` json
{
    "id":1,
    "username":"user1",
    "password":"pass",
    "email":"user1@email.com",
    "posts":[
        {
            "id":1,
            "string":"this is user1\u0027s post",
            "user_id":1,
            "created_at":{
                "date":"2015-06-24 11:28:52.000000",
                "timezone_type":3,
                "timezone":"UTC"
            },
            "updated_at":{
                "date":"2015-06-24 11:28:52.000000",
                "timezone_type":3,
                "timezone":"UTC"
            },
            "poster":{
                "id":1,
                "username":"user1",
                "password":"pass",
                "email":"user1@email.com"
            }
        },
        {
            "id":4,
            "string":"another user1 post",
            "user_id":1,
            "created_at":{
                "date":"2015-06-24 11:28:52.000000",
                "timezone_type":3,
                "timezone":"UTC"
            },
            "updated_at":{
                "date":"2015-06-24 11:28:52.000000",
                "timezone_type":3,
                "timezone":"UTC"
            },
            "poster":{
                "id":1,
                "username":"user1",
                "password":"pass",
                "email":"user1@email.com"
            }
        }
    ],
    "post_count":2
}
```