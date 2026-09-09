module.exports = {
  apps: [
    {
      name: 'wa-service',
      script: './server.js',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        NODE_ENV: 'production',
        PORT: 3000,
        WA_SERVICE_API_KEY: 'kp_wa_secret_key_2026'
      }
    }
  ]
};
