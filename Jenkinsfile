pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                echo "🔄 Cloning code from GitHub..."
                git branch: 'main', url: 'https://github.com/Tam-Half/Simple-CI.git'
            }
        }
        stage('Test Connection') {
            steps {
                echo "✅ Jenkins pipeline running successfully!"
                sh 'ls -l'
            }
        }
    }

    post {
        success {
            echo "🎉 Pipeline test completed OK!"
        }
        failure {
            echo "❌ Something went wrong!"
        }
    }
}
