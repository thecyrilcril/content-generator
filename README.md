### Setup

1. Clone project ```git@github.com:thecyrilcril/content-generator.git``` or ```https://github.com/thecyrilcril/content-generator.git```
2. run ```cd content-generator```
3. run ```composer intsall```
4. run ```cp .env.example .env```
5. Update the ```.env```file with your prefered values for:
```OPENAI_API_KEY=```
```OPENAI_ORGANIZATION=```
```OPENAI_CHAT_MODEL=```
```OPENAI_IMAGE_MODEL=```
6. run ```php artisan key:generate``` to generate a new app wide encryption key
7. run ```npm install```
8. run ```npm run build```
9. run ```php artisan serve``` to serve the project

