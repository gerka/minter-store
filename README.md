# minter-store
MNTSHOP  crypto that uses plugin Minter-Store for Wordpress example can be found here https://mntshop.ru
## Plugin minter-store
Hello! It's readme about structure, features and how to support this plugin 

Firstly about Structure 

 - Assets (storage plugin assets like myscript.js)
 - templates (storage some pages to manage contents in admin section or in public view)
 - includes (Store logic clases of the plugin)
 - includes/Base (Store base logic of the plugin like add links, enqueue scripts, and other controllers)
 - includes/Pages (Store logic for sections like admin page section)
 - includes/API (Store logic for work with WP)
```$xslt
And Please call self methods from BaseController to include constants to your code.
If you want automatically construct use register() method instead of standard __construct().
To debug and logs please use this method self::Log(' minter-plugin  '); to view logs please turn it on in BaseController::$debug

```