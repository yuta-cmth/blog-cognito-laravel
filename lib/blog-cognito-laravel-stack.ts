import * as cdk from "aws-cdk-lib";
import { Construct } from "constructs";
import * as cognito from "aws-cdk-lib/aws-cognito";

export class BlogCognitoLaravelStack extends cdk.Stack {
  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    // Create Cognito userpool so that laravel can use it. Use hosted UI.
    const userPool = new cognito.UserPool(this, "UserPool", {
      userPoolName: "blog-cognito-laravel-user-pool",
      selfSignUpEnabled: true,
      signInAliases: { username: true },
      autoVerify: { email: true },
      standardAttributes: {
        email: {
          required: true,
          mutable: true,
        },
      },
      passwordPolicy: {
        minLength: 6,
        requireLowercase: false,
        requireDigits: false,
        requireSymbols: false,
        requireUppercase: false,
      },
      accountRecovery: cognito.AccountRecovery.EMAIL_ONLY,
      removalPolicy: cdk.RemovalPolicy.DESTROY,
    });

    // callback to localhost
    const client = userPool.addClient("UserPoolClient", {
      userPoolClientName: "laravel",
      generateSecret: true,
      oAuth: {
        flows: {
          authorizationCodeGrant: true,
        },
        scopes: [cognito.OAuthScope.OPENID],
        callbackUrls: ["http://localhost/cognito/login-cb"],
        logoutUrls: ["http://localhost/cognito/logout-cb"],
      },
      refreshTokenValidity: cdk.Duration.minutes(60),
    });

    // Hosted UI
    const domainPrefix = `blog-laravel-${this.account}`;
    const domain = userPool.addDomain("UserPoolDomain", {
      cognitoDomain: {
        domainPrefix,
      },
    });

    new cdk.CfnOutput(this, "CognitoUserPoolId", {
      value: userPool.userPoolId,
    });
    new cdk.CfnOutput(this, "CognitoClientId", {
      value: client.userPoolClientId,
    });
    new cdk.CfnOutput(this, "HostedUIDomain", {
      value: `${domainPrefix}.auth.${this.region}.amazoncognito.com`,
    });
  }
}
