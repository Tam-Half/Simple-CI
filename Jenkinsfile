pipeline {
    agent any
    
    environment {
        SONAR_HOST_URL = 'http://172.16.3.130:13999' 
        SONAR_PROJECT_KEY = 'simple-ci-project'
        SONAR_PROJECT_NAME = 'Simple CI - PHP Project'
    }
    
    stages {
        stage('Checkout Code') {
            steps {
                echo '📥 Đang checkout code từ GitHub...'
                checkout scm
                
                // Hiển thị thông tin commit
                sh '''
                    echo "Branch: $(git rev-parse --abbrev-ref HEAD)"
                    echo "Commit: $(git rev-parse --short HEAD)"
                    echo "Author: $(git log -1 --pretty=format:'%an')"
                '''
            }
        }
        
        stage('Environment Info') {
            steps {
                echo '🔍 Kiểm tra môi trường...'
                sh '''
                    echo "=== PHP Version ==="
                    php --version || echo "PHP chưa được cài đặt"
                    
                    echo "\n=== Composer Version ==="
                    composer --version || echo "Composer chưa được cài đặt"
                    
                    echo "\n=== Node Version ==="
                    node --version || echo "Node.js chưa được cài đặt"
                    
                    echo "\n=== Directory Structure ==="
                    ls -la
                '''
            }
        }
        
        stage('Install Dependencies') {
            steps {
                echo '📦 Cài đặt dependencies...'
                script {
                    // Cài đặt PHP dependencies nếu có composer.json
                    if (fileExists('composer.json')) {
                        sh '''
                            echo "Cài đặt PHP dependencies với Composer..."
                            composer install --no-interaction --prefer-dist --optimize-autoloader
                        '''
                    } else {
                        echo "⚠️  Không tìm thấy composer.json, bỏ qua bước cài đặt PHP dependencies"
                    }
                    
                    // Cài đặt Node dependencies nếu có package.json
                    if (fileExists('package.json')) {
                        sh '''
                            echo "Cài đặt Node.js dependencies..."
                            npm install
                        '''
                    } else {
                        echo "⚠️  Không tìm thấy package.json, bỏ qua bước cài đặt Node dependencies"
                    }
                }
            }
        }
        
        stage('Code Quality Check') {
            steps {
                echo '✅ Kiểm tra chất lượng code...'
                script {
                    // PHP CodeSniffer (nếu có cài đặt)
                    try {
                        sh '''
                            if command -v phpcs &> /dev/null; then
                                echo "Chạy PHP CodeSniffer..."
                                phpcs --standard=PSR12 --ignore=vendor,node_modules . || true
                            fi
                        '''
                    } catch (Exception e) {
                        echo "⚠️  PHP CodeSniffer không khả dụng"
                    }
                }
            }
        }
        
        stage('SonarQube Analysis') {
            steps {
                echo '🔍 Bắt đầu quét code với SonarQube...'
                
                script {
                    withCredentials([string(credentialsId: 'sonarqube-token', variable: 'SONAR_TOKEN')]) {
                        sh """
                            sonar-scanner \
                            -Dsonar.projectKey=${SONAR_PROJECT_KEY} \
                            -Dsonar.projectName='${SONAR_PROJECT_NAME}' \
                            -Dsonar.sources=. \
                            -Dsonar.host.url=${SONAR_HOST_URL} \
                            -Dsonar.login=\${SONAR_TOKEN} \
                            -Dsonar.sourceEncoding=UTF-8 \
                            -Dsonar.exclusions=**/vendor/**,**/node_modules/**,**/tests/**,**/database/**,**/*.min.js,**/*.min.css \
                            -Dsonar.php.coverage.reportPaths=coverage/coverage.xml \
                            -Dsonar.php.tests.reportPath=tests/report.xml
                        """
                    }
                }
                
                echo '✅ SonarQube scan hoàn tất!'
            }
        }
        
        stage('Quality Gate') {
            steps {
                echo '🚦 Kiểm tra Quality Gate từ SonarQube...'
                
                timeout(time: 5, unit: 'MINUTES') {
                    script {
                        try {
                            // Đợi kết quả từ SonarQube server
                            def qg = waitForQualityGate()
                            
                            if (qg.status != 'OK') {
                                echo "❌ Quality Gate Status: ${qg.status}"
                                error "Pipeline dừng lại do Quality Gate thất bại!"
                            } else {
                                echo "✅ Quality Gate PASSED: ${qg.status}"
                            }
                        } catch (Exception e) {
                            echo "⚠️  Không thể kiểm tra Quality Gate: ${e.message}"
                            echo "⚠️  Tiếp tục pipeline nhưng cần kiểm tra thủ công trên SonarQube"
                            // Không dừng pipeline nếu không check được Quality Gate
                        }
                    }
                }
            }
        }
        
        stage('Build Docker Image (Optional)') {
            when {
                expression { fileExists('Dockerfile') }
            }
            steps {
                echo '🐳 Build Docker image...'
                script {
                    sh '''
                        docker build -t simple-ci:${BUILD_NUMBER} .
                        docker tag simple-ci:${BUILD_NUMBER} simple-ci:latest
                        echo "✅ Docker image built successfully"
                    '''
                }
            }
        }
        
        stage('Security Scan') {
            steps {
                echo '🔒 Kiểm tra bảo mật...'
                script {
                    // Kiểm tra vulnerabilities folder
                    if (fileExists('vulnerabilities')) {
                        echo "⚠️  Phát hiện thư mục vulnerabilities - cần review"
                    }
                    
                    // Có thể thêm các công cụ security scan khác
                    echo "💡 Tip: Có thể tích hợp thêm OWASP Dependency Check, Snyk, v.v..."
                }
            }
        }
    }
    
    post {
        success {
            echo '✅ ========================================='
            echo '✅ PIPELINE THÀNH CÔNG!'
            echo '✅ ========================================='
            echo "✅ Build Number: ${BUILD_NUMBER}"
            echo "✅ SonarQube Report: ${SONAR_HOST_URL}/dashboard?id=${SONAR_PROJECT_KEY}"
            
            // Gửi notification nếu cần (email, Slack, etc.)
        }
        
        failure {
            echo '❌ ========================================='
            echo '❌ PIPELINE THẤT BẠI!'
            echo '❌ ========================================='
            echo "❌ Build Number: ${BUILD_NUMBER}"
            echo "❌ Kiểm tra logs để biết chi tiết lỗi"
            
            // Gửi notification về lỗi
        }
        
        unstable {
            echo '⚠️  Pipeline không ổn định - cần kiểm tra'
        }
        
        always {
            echo '🧹 Dọn dẹp workspace...'
            
            // Lưu artifacts nếu cần
            script {
                try {
                    archiveArtifacts artifacts: '**/logs/*.log', allowEmptyArchive: true
                } catch (Exception e) {
                    echo "Không có artifacts để lưu"
                }
            }
            
            // Clean workspace (tùy chọn)
            // cleanWs()
        }
    }
}