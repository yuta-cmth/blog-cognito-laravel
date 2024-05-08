```bash
cdk deploy --require-approval never
cd codes/blog-cognito-laravel/

export AWS_ACCOUNT_ID=<your account id>;
export AWS_REGION=<your region>;
export AWS_COGNITO_USER_POOL_ID=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`CognitoUserPoolId`].OutputValue');
export AWS_COGNITO_CLIENT_ID=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`CognitoClientId`].OutputValue');
export AWS_COGNITO_HOSTED_UI_DOMAIN=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`HostedUIDomain`].OutputValue');
export AWS_COGNITO_CLIENT_SECRET=$(aws cognito-idp describe-user-pool-client --user-pool-id $AWS_COGNITO_USER_POOL_ID --client-id $AWS_COGNITO_CLIENT_ID --output text --query "UserPoolClient.ClientSecret");

aws cognito-idp admin-create-user --user-pool-id $AWS_COGNITO_USER_POOL_ID --temporary-password password --username laravel-test --user-attributes Name=email,Value=<your email address>

./vendor/bin/sail up -d;
./vendor/bin/sail artisan migrate;
./vendor/bin/sail artisan config:clear;
./vendor/bin/sail artisan config:cache;
```
