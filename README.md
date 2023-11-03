# Laravel Shared Hosting Cron Alternative

This package is designed for Laravel applications hosted on shared hosting environments that don't permit running cron jobs every minute. It ensures that the `artisan schedule:run` command is executed every minute and efficiently handles any terminated processes.

### Handling Host Providers' Time Limitations

If your hosting provider restricts the execution time of your scripts, you can use the `--stop-after-minutes` option to specify a time limit. This will automatically terminate the script after running for the specified duration (X minutes).
