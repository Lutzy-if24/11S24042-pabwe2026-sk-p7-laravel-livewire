pipeline {
    agent {
        docker {
            image 'laravelsail/php84-composer:latest'
            args '-u root'
        }
    }

    environment {
        APP_ENV = 'testing'
        APP_KEY = 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA='
        DB_CONNECTION = 'sqlite'
        DB_DATABASE = ':memory:'
        XDEBUG_MODE = 'coverage'
    }

    stages {
        stage('Composer Install') {
            steps {
                sh 'php -m | grep -i gd'
                sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
            }
        }

        stage('Composer Audit') {
            steps {
                sh 'composer audit'
            }
        }

        stage('Frontend Build') {
            steps {
                sh 'npm ci'
                sh 'npm run build'
            }
        }

        stage('Test & Coverage (100% Required)') {
            steps {
                sh 'php artisan test --coverage --min=100'
            }
        }

        stage('Security Scan (Trivy)') {
            steps {
                sh 'which trivy > /dev/null 2>&1 || curl -sfL https://raw.githubusercontent.com/aquasecurity/trivy/main/contrib/install.sh | sh -s -- -b /usr/local/bin'
                sh 'trivy fs --exit-code 1 --severity HIGH,CRITICAL --scanners vuln,secret .'
            }
        }

        stage('Package Artifact') {
            steps {
                sh 'zip -r latest-app.zip . -x "vendor/*" -x "node_modules/*" -x ".git/*" -x ".phpunit.cache/*"'
                archiveArtifacts artifacts: 'latest-app.zip', fingerprint: true
            }
        }
    }

    post {
        always {
            echo 'Pipeline execution completed.'
        }
    }
}
