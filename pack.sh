rm -rf `find -name '.git' -type d`
rm -rf `find -name 'doc' -type d`
rm -rf `find -name 'docs' -type d`
rm -rf `find -name 'test' -type d`
rm -rf `find -name 'tests' -type d`
rm -rf `find -name 'Tests' -type d`
rm -rf `find -name 'Test' -type d | grep -v ./vendor/twig/twig/lib/Twig/Node/Expression/Test`
rm -rf `find -name 'cache' -type d | grep -v ./vendor/doctrine/cache`
rm -rf vendor/bolt/bolt/files
rm -rf vendor/bolt/bolt/theme
rm -rf vendor/bolt/bolt/app/view/css
rm -rf vendor/bolt/bolt/app/view/font
rm -rf vendor/bolt/bolt/app/view/img
rm -rf vendor/bolt/bolt/app/view/js
rm -rf vendor/bolt/bolt/app/view/lib
rm -rf vendor/bolt/bolt/app/classes/markdownify
rm -rf vendor/bolt/bolt/app/classes/upload
rm -rf vendor/bolt/bolt/app/extensions/HelloWorld
rm -rf vendor/bolt/bolt/app/extensions/SimpleForms
rm -rf vendor/bolt/bolt/app/extensions/PlusOne
rm -rf vendor/bolt/bolt/app/extensions/Editable
rm -rf vendor/bolt/bolt/app/extensions/PasswordProtect
rm -rf vendor/bolt/bolt/app/extensions/TwitterFeed
rm -rf vendor/bolt/bolt/app/extensions/TwitterButton
rm -rf vendor/bolt/bolt/app/extensions/Disqus
rm -rf vendor/bolt/bolt/app/extensions/Authenticate
rm -rf vendor/bolt/bolt/app/extensions/NiceUrls
rm -rf vendor/bolt/bolt/app/extensions/RelatedContentByTags
rm -rf vendor/bolt/bolt/app/extensions/JSONAccess
rm -rf vendor/bolt/bolt/app/extensions/GoogleAnalytics
rm -rf vendor/bolt/bolt/app/extensions/RSSFeed
rm -rf vendor/bolt/bolt/app/extensions/MetaTags
rm -rf vendor/bolt/bolt/app/extensions/ImportWXR
rm -rf vendor/bolt/bolt/app/extensions/PinIt
rm -rf vendor/bolt/bolt/app/extensions/WaffleOrders
rm -rf vendor/bolt/bolt/app/extensions/RSSAggregator
rm -rf vendor/bolt/bolt/app/extensions/FacebookComments
rm -rf vendor/bolt/bolt/app/extensions/RateIt
rm -rf vendor/bolt/bolt/app/extensions/FacebookLike
rm -rf vendor/bolt/bolt/app/extensions/MenuEditor
rm -rf vendor/bolt/bolt/app/extensions/Redirector
rm -rf vendor/bolt/bolt/app/extensions/Socialite
rm -rf vendor/bolt/bolt/app/extensions/TitleCase
rm -rf vendor/bolt/bolt/app/extensions/TestSnippets
rm -rf vendor/twbs/bootstrap-sass
rm -rf vendor/richardhinkamp/kcfinder
rm -rf vendor/symfony/icu/Symfony/Component/Icu/Resources/
rm -rf src/sass
rm -rf web/files
rm -rf web/fancybox/demo
rm -rf .idea
rm -f config/config_local.yml
rm pack.sh
mkdir -p cache
