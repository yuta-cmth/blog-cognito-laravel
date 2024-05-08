## Setup IAM policies and Cloud9 to deploy the resources

See [Calling AWS services from an environment in AWS Cloud9](https://docs.aws.amazon.com/cloud9/latest/user-guide/credentials.html#credentials-temporary) for more information.

```bash
# Create a role
aws iam create-role --role-name blog-cognito-laravel-cloud9-instance-role --assume-role-policy-document file://cloud9_iam/trust-policy.json;

# Attach policies to the role
aws iam attach-role-policy --role-name blog-cognito-laravel-cloud9-instance-role --policy-arn arn:aws:iam::aws:policy/AWSCloud9SSMInstanceProfile;
aws iam put-role-policy --role-name blog-cognito-laravel-cloud9-instance-role --policy-name blog-cognito-laravel-cloud9-instance-policy --policy-document file://cloud9_iam/inline-policy.json;
aws iam create-instance-profile --instance-profile-name blog-cognito-laravel-cloud9-instance-profile;
aws iam add-role-to-instance-profile --instance-profile-name blog-cognito-laravel-cloud9-instance-profile --role-name blog-cognito-laravel-cloud9-instance-role;

# Start Cloud9 environment and attach the role to the EC2 instance used by Cloud9
read -s c9_subnet_id; export c9_subnet_id;		# Use your own subnet id.
read -s c9_owner_arn; export c9_owner_arn;		# Use your own owner arn. You can run `aws sts get-caller-identity --query Arn --output text` on CloudShell if you use currently logged-in account.

aws cloud9 create-environment-ec2 \
	--name blog-cognito-laravel-cloud9-environment \
	--description "Cloud9 environment for blog-cognito-laravel" \
	--instance-type t3.small \
	--automatic-stop-time-minutes 60 \
	--image-id amazonlinux-2023-x86_64 \
	--connection-type CONNECT_SSM \
	--subnet-id $c9_subnet_id \
	--owner-arn $c9_owner_arn;

# Disassociate default instance profile and associate the instance profile created above to the Cloud9 instance.
c9_instance_id=$(aws ec2 describe-instances --filters Name=tag:Name,Values=*blog-cognito-laravel-cloud9-environment* Name=instance-state-name,Values=running --query "Reservations[*].Instances[*].InstanceId" --output text);
default_iipa_id=$(aws ec2 describe-iam-instance-profile-associations --filters "Name=instance-id,Values=$c9_instance_id" --query "IamInstanceProfileAssociations[0].AssociationId" --output text);
aws ec2 disassociate-iam-instance-profile --association-id $default_iipa_id;
aws ec2 associate-iam-instance-profile --iam-instance-profile Name=blog-cognito-laravel-cloud9-instance-profile --instance-id $c9_instance_id;

# Lastly, disable the temporary credentials for the Cloud9 environment in the IDE.
```

## On Cloud9 IDE

```bash
git clone https://github.com/yuta-cmth/blog-cognito-laravel.git
cd blog-cognito-laravel/
npm i
cdk deploy --require-approval never

export AWS_COGNITO_USER_POOL_ID=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`CognitoUserPoolId`].OutputValue');

aws cognito-idp admin-create-user --user-pool-id $AWS_COGNITO_USER_POOL_ID --temporary-password password --username laravel-test --user-attributes Name=email,Value=<your email address>
```

## On your computer

```bash
git clone https://github.com/yuta-cmth/blog-cognito-laravel.git
cd blog-cognito-laravel/codes/blog-cognito-laravel

export AWS_ACCOUNT_ID=<your account id>;
export AWS_REGION=<your region>;
export AWS_COGNITO_USER_POOL_ID=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`CognitoUserPoolId`].OutputValue');
export AWS_COGNITO_CLIENT_ID=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`CognitoClientId`].OutputValue');
export AWS_COGNITO_HOSTED_UI_DOMAIN=$(aws cloudformation describe-stacks --stack-name BlogCognitoLaravelStack --output text --query 'Stacks[0].Outputs[?OutputKey==`HostedUIDomain`].OutputValue');
export AWS_COGNITO_CLIENT_SECRET=$(aws cognito-idp describe-user-pool-client --user-pool-id $AWS_COGNITO_USER_POOL_ID --client-id $AWS_COGNITO_CLIENT_ID --output text --query "UserPoolClient.ClientSecret");

./vendor/bin/sail up -d;
./vendor/bin/sail artisan migrate;
./vendor/bin/sail artisan config:clear;
./vendor/bin/sail artisan config:cache;
```

## Clean up

```bash
cdk destroy --force

# Delete Cloud9 environment and IAM role.
aws cloud9 delete-environment --environment-id <environment_id>
aws iam remove-role-from-instance-profile --instance-profile-name blog-cognito-laravel-cloud9-instance-profile --role-name blog-cognito-laravel-cloud9-instance-role;
aws iam delete-instance-profile --instance-profile-name blog-cognito-laravel-cloud9-instance-profile;
aws iam detach-role-policy --role-name blog-cognito-laravel-cloud9-instance-role --policy-arn arn:aws:iam::aws:policy/AWSCloud9SSMInstanceProfile;
aws iam detach-role-policy --role-name blog-cognito-laravel-cloud9-instance-role --policy-arn arn:aws:iam::aws:policy/AmazonS3FullAccess;
aws iam delete-role-policy --role-name blog-cognito-laravel-cloud9-instance-role --policy-name blog-cognito-laravel-cloud9-instance-policy;
aws iam delete-role --role-name blog-cognito-laravel-cloud9-instance-role;
```
