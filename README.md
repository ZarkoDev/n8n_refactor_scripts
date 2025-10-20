## Setup project

composer install

chmod 777 -R storage bootstrap public

update database credentials in .env, eventually webhooks

docker-compose down --volumes

docker compose up -d --build

docker-compose exec -T laravel-app php artisan test

## N8n setup

Go to http://localhost:5678/

Add Bearer Auth credential with `Ym9zY236Ym9zY28=` as we use it in .env file

Add Gemini API as it is free. Or use ChatGPT but swap Gemini with ChatGPT in the workflow.

Import workflows from n8n/workflows

Activate workflows

## Architecture diagram

https://www.mermaidchart.com/d/1c47d38b-07ce-41bc-bd34-eac03639a09c

## How to use it

docker-compose exec laravel-app php artisan queue:work

Create ad-script

## Postman collection

You can find postman collection in `postman` folder.
