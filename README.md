Opauth-Tumblr
================
[Opauth][1] strategy for Tumblr authentication.

Implemented based on http://www.tumblr.com/docs/en/api/v2#auth

Opauth is a multi-provider authentication framework for PHP.

Getting started
----------------
1. Install Opauth-Tumblr:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/jelled/opauth-tumblr.git Tumblr
   ```

2. Create a Tumblr application at http://www.tumblr.com/oauth/apps

3. Configure Opauth-Tumblr strategy.

4. Direct user to `http://path_to_opauth/tumblr` to authenticate

5. Now that you have the user's token and token_secret, I recomend tumbler's [official php library][3] to create/edit/delete posts.

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Tumblr' => array(
  'consumer_key' => 'YOUR CLIENT ID',
  'consumer_secret' => 'YOUR CLIENT SECRET'
)
```

Optional parameters:
`scope`, `response_type`  
For `scope`, separate each scopes with a space(' ') and not a plus sign ('+'). Eg. `likes comments`.


References
----------
- http://www.tumblr.com/docs/en/api/v2#auth

License
---------
Opauth-Tumblr is MIT Licensed
Copyright © 2013 Benjamin Bjurstrom ([@benbjurstrom][2])

[1]: https://github.com/opauth/opauth
[2]: http://twitter.com/benbjurstrom
[3]: https://github.com/tumblr/tumblr.php