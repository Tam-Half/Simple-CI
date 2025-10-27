pipeline {
    agent any

    environment {
        SONAR_HOST_URL = 'http://172.16.3.130:13999'
        SONAR_PROJECT_KEY = 'simple-ci-project'
        SONAR_PROJECT_NAME = 'Simple CI - PHP Project'
        PATH = "/opt/sonar-scanner/bin:${env.PATH}"  // đảm bảo sonar-scanner chạy được
    }

    stages {
        stage('Checkout Code') {
            steps {
                echo '📥 Đang checkout code từ GitHub...'
                checkout scm
            }
        }

        stage('Environment Info') {
            steps {
                sh '''
                    echo "=== PHP Version ==="
                    php --version || echo "PHP chưa được cài"
                    echo "=== Composer Version ==="
                    composer --version || echo "Composer chưa được cài"
                '''
            }
        }

        stage('Install Dependencies') {
            steps {
                script {
                    if (fileExists('composer.json')) {
                        sh 'composer install --no-interaction --prefer-dist'
                    }
                }
            }
        }

        stage('SonarQube Scan') {
            steps {
                withSonarQubeEnv('SonarQube-Server') {  
                    withCredentials([string(credentialsId: 'sonarqube-token', variable: 'sonarqube-token')]) {
                        sh """
                            sonar-scanner \
                            -Dsonar.projectKey=${SONAR_PROJECT_KEY} \
                            -Dsonar.projectName="${SONAR_PROJECT_NAME}" \
                            -Dsonar.sources=. \
                            -Dsonar.host.url=${SONAR_HOST_URL} \
                            -Dsonar.login=${SONAR_TOKEN} \
                            -Dsonar.sourceEncoding=UTF-8 \
                            -Dsonar.exclusions=**/vendor/**,**/node_modules/** \
                            -Dsonar.php.coverage.reportPaths=coverage/coverage.xml
                        """
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                timeout(time: 3, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }
    }

    post {
        success {
            echo "✅ DONE | Xem báo cáo: ${SONAR_HOST_URL}/dashboard?id=${SONAR_PROJECT_KEY}"
        }
        failure {
            echo "❌ Pipeline Failed"
        }
    }
}
