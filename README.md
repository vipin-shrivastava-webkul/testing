# Community Packages - Form Builder

The Form Builder application allows you to make customisable forms as per your business requirements. The user can seamlessly embed the form code ino their own websites. It's drag and drop functionality allows you to create beautiful and effective forms without having to write any HTML or CSS of your own. 
You can add as many labels within the Form Builder, and create labels of your own, the Custom Fields within the Form Component. When the form is submitted, a UVdesk ticket will be generated automatically.
For customizing the look of the Form, you can change the CSS within the application file. 

## Installation

In the root of your UVdesk project, go inside the apps folder and create a new directory called uvdesk.

Inside the uvdesk directorys, your form-component directory will exist and will contain the application; directly download the application and place it within the respective directory or clone the application from within the uvdesk directory.

### Configuration

Run the given commands below, inside the project root:

```bash
$ php bin/console uvdesk_extensions:configure-extensions
$ php bin/console assets:install
$ php bin/console doctrine:migrations:diff
$ php bin/console doctrine:migrations:migrate
```

Finally clear your project cache using below command:

```bash
$ php bin/console c:c
```

Now your Form builder application should be up and running.
