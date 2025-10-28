pipeline {
    agent { label 'jenkinsagent' }

    environment {
        SONAR_HOST_URL = 'http://172.16.3.130:13999/'
        SONAR_PROJECT_KEY = 'simple-ci-project'
        SONAR_PROJECT_NAME = 'Simple CI - PHP Project'
        PATH = "/home/jenkinsUser/sonar-qube/bin:${env.PATH}"
    }

    stages {
        stage('Checkout Code') {
            steps {
                echo ' Đang checkout code từ GitHub...'
                checkout scm
            }
        }

        stage('Environment Info') {
            steps {
                sh '''
                    echo "=== PHP Version ==="
                    php --version || echo "⚠️ PHP chưa được cài trên Agent"
                    echo "=== Composer Version ==="
                    composer --version || echo "⚠️ Composer chưa được cài trên Agent"
                '''
            }
        }

        stage('Install Dependencies') {
            when {
                expression { fileExists('composer.json') }
            }
            steps {
                sh '''
                    echo " Cài dependency bằng Composer..."
                    composer install --no-interaction --prefer-dist
                '''
            }
        }

        stage('SonarQube Scan') {
            steps {
                withSonarQubeEnv('SonarQube-Server') {
                    withCredentials([string(credentialsId: 'sonar-qube-scanner', variable: 'SONAR_TOKEN')]) {
                        sh(script: """
                            sonar-scanner -X \
                            -Dsonar.projectKey=${SONAR_PROJECT_KEY} \
                            -Dsonar.projectName="${SONAR_PROJECT_NAME}" \
                            -Dsonar.sources=. \
                            -Dsonar.host.url=${SONAR_HOST_URL} \
                            -Dsonar.login=$SONAR_TOKEN \
                            -Dsonar.sourceEncoding=UTF-8 \
                            -Dsonar.exclusions=**/vendor/**,**/node_modules/**
                        """)
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
            echo "✅ DONE | Xem báo cáo SonarQube tại: ${SONAR_HOST_URL}/dashboard?id=${SONAR_PROJECT_KEY}"
        }
        failure {
            echo " Pipeline Failed"
        }
    }
}
// cmt test 12321321
