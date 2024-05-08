#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from 'aws-cdk-lib';
import { BlogCognitoLaravelStack } from '../lib/blog-cognito-laravel-stack';

const app = new cdk.App();
new BlogCognitoLaravelStack(app, "BlogCognitoLaravelStack", {
  env: {
    account: process.env.CDK_DEFAULT_ACCOUNT,
    region: process.env.CDK_DEFAULT_REGION,
  },
});