#!/usr/bin/env python3
import sys, json, time, re
from collections import defaultdict

# Enhanced AI task generator with intelligent text analysis
# Analyzes project description to extract:
# - Key features and requirements
# - Technical components
# - Dependencies and priorities
# - Generates hierarchical task breakdown

def detect_language(text: str) -> str:
    """Detect language from text using langdetect or fallback to 'en'"""
    try:
        import langdetect
        return langdetect.detect(text)
    except Exception:
        return 'en'


class TaskAnalyzer:
    """Analyzes project description and generates structured task breakdown"""
    
    # Comprehensive keywords for identifying different aspects of a project
    # Based on software engineering, project management, and industry best practices
    
    FEATURE_KEYWORDS = [
        # Core feature terms
        'feature', 'functionality', 'capability', 'module', 'component', 'system',
        'service', 'tool', 'utility', 'widget', 'plugin', 'extension', 'addon',
        
        # UI/UX elements
        'interface', 'dashboard', 'panel', 'console', 'portal', 'page', 'screen',
        'view', 'form', 'dialog', 'modal', 'popup', 'menu', 'navigation', 'navbar',
        'sidebar', 'toolbar', 'footer', 'header', 'layout', 'template', 'theme',
        'wizard', 'carousel', 'slider', 'gallery', 'grid', 'table', 'list',
        
        # User interaction
        'button', 'input', 'field', 'dropdown', 'select', 'checkbox', 'radio',
        'toggle', 'switch', 'search', 'filter', 'sort', 'pagination', 'upload',
        'download', 'export', 'import', 'preview', 'editor', 'viewer',
        
        # Business features
        'workflow', 'process', 'automation', 'integration', 'synchronization',
        'notification', 'alert', 'reminder', 'calendar', 'scheduler', 'booking',
        'reservation', 'appointment', 'registration', 'subscription', 'membership',
        'profile', 'account', 'settings', 'preferences', 'configuration',
        
        # E-commerce & transactions
        'cart', 'checkout', 'payment', 'invoice', 'receipt', 'order', 'purchase',
        'transaction', 'billing', 'pricing', 'discount', 'coupon', 'voucher',
        'refund', 'shipping', 'delivery', 'tracking', 'inventory', 'catalog',
        'product', 'item', 'listing', 'marketplace', 'store', 'shop',
        
        # Communication & social
        'chat', 'messaging', 'comment', 'feedback', 'review', 'rating', 'like',
        'share', 'follow', 'subscribe', 'feed', 'timeline', 'post', 'article',
        'blog', 'forum', 'discussion', 'thread', 'reply', 'mention', 'tag',
        
        # Content management
        'content', 'media', 'document', 'file', 'attachment', 'image', 'video',
        'audio', 'gallery', 'library', 'repository', 'archive', 'storage',
        'folder', 'directory', 'collection', 'album', 'playlist',
        
        # Analytics & reporting
        'report', 'analytics', 'statistics', 'metrics', 'dashboard', 'chart',
        'graph', 'visualization', 'insight', 'summary', 'overview', 'snapshot',
        'export', 'log', 'audit', 'history', 'activity', 'tracking',

        # User management & collaboration
        'team', 'group', 'organization', 'department', 'role', 'permission',
        'admin', 'moderator', 'guest', 'user management', 'access control',
        'collaboration', 'coauthoring', 'multiplayer', 'real-time editing',

        # Personalization & customization
        'theme', 'skin', 'branding', 'logo', 'color scheme', 'dark mode',
        'localization', 'internationalization', 'translation', 'language',
        'customization', 'personalization', 'profile picture', 'avatar',

        # Advanced workflow
        'approval', 'escalation', 'delegation', 'handoff', 'task assignment',
        'kanban', 'scrum', 'sprint', 'backlog', 'milestone', 'roadmap',

        # Knowledge & learning
        'knowledge base', 'faq', 'guide', 'manual', 'tutorial', 'training',
        'onboarding', 'walkthrough', 'helpdesk', 'support ticket',

        # AI-driven features
        'recommendation', 'suggestion', 'prediction', 'forecast', 'classification',
        'clustering', 'translation', 'summarization', 'assistant', 'bot',

        # Compliance & auditing
        'policy', 'terms', 'conditions', 'consent', 'privacy', 'compliance',
        'gdpr', 'hipaa', 'audit trail', 'governance', 'reporting dashboard'
    ]
    
    TECH_KEYWORDS = [
        # Backend & APIs
        'api', 'rest', 'restful', 'graphql', 'soap', 'grpc', 'websocket', 'webhook',
        'endpoint', 'route', 'controller', 'service', 'middleware', 'handler',
        'microservice', 'serverless', 'lambda', 'function', 'worker', 'job', 'queue',
        
        # Database & storage
        'database', 'db', 'sql', 'nosql', 'mysql', 'postgresql', 'mongodb', 'redis',
        'elasticsearch', 'cassandra', 'dynamodb', 'firebase', 'supabase',
        'orm', 'query', 'migration', 'schema', 'model', 'entity', 'repository',
        'transaction', 'index', 'cache', 'caching', 'memcached', 'storage',
        'blob', 's3', 'cdn', 'cloudinary', 'bucket',
        
        # Frontend & UI
        'frontend', 'front-end', 'ui', 'ux', 'interface', 'client', 'browser',
        'react', 'vue', 'angular', 'svelte', 'nextjs', 'nuxt', 'gatsby',
        'html', 'css', 'javascript', 'typescript', 'jsx', 'tsx', 'sass', 'scss',
        'tailwind', 'bootstrap', 'material', 'responsive', 'mobile', 'desktop',
        'spa', 'pwa', 'ssr', 'ssg', 'hydration', 'rendering',
        
        # Authentication & authorization
        'authentication', 'auth', 'authorization', 'login', 'logout', 'signin',
        'signup', 'register', 'password', 'token', 'jwt', 'oauth', 'oauth2',
        'saml', 'sso', 'ldap', 'active directory', 'session', 'cookie',
        'permission', 'role', 'rbac', 'acl', 'access control', 'privilege',
        '2fa', 'mfa', 'otp', 'biometric', 'captcha', 'recaptcha',
        
        # Security
        'security', 'encryption', 'decryption', 'hashing', 'ssl', 'tls', 'https',
        'certificate', 'firewall', 'waf', 'ddos', 'xss', 'csrf', 'sql injection',
        'sanitization', 'validation', 'cors', 'csp', 'vulnerability', 'penetration',
        'audit', 'compliance', 'gdpr', 'hipaa', 'pci', 'iso', 'soc2',
        
        # Testing & quality
        'testing', 'test', 'unit test', 'integration test', 'e2e', 'end-to-end',
        'acceptance test', 'regression', 'smoke test', 'load test', 'stress test',
        'performance test', 'security test', 'penetration test', 'qa', 'quality',
        'coverage', 'mock', 'stub', 'fixture', 'assertion', 'jest', 'mocha',
        'cypress', 'selenium', 'playwright', 'puppeteer', 'junit', 'pytest',
        
        # DevOps & deployment
        'deployment', 'deploy', 'ci', 'cd', 'cicd', 'pipeline', 'build', 'release',
        'docker', 'container', 'kubernetes', 'k8s', 'helm', 'terraform', 'ansible',
        'jenkins', 'gitlab', 'github actions', 'circleci', 'travis', 'azure devops',
        'aws', 'azure', 'gcp', 'cloud', 'infrastructure', 'iaas', 'paas', 'saas',
        'ec2', 'ecs', 'eks', 'fargate', 'lambda', 'cloudformation', 'cloudwatch',
        'monitoring', 'logging', 'alerting', 'observability', 'apm', 'tracing',
        'prometheus', 'grafana', 'elk', 'datadog', 'newrelic', 'sentry',
        
        # Performance & optimization
        'performance', 'optimization', 'speed', 'latency', 'throughput', 'scalability',
        'load balancing', 'clustering', 'sharding', 'partitioning', 'replication',
        'caching', 'cdn', 'compression', 'minification', 'bundling', 'lazy loading',
        'code splitting', 'tree shaking', 'prefetch', 'preload', 'async', 'parallel',
        
        # Architecture & design
        'architecture', 'design', 'pattern', 'mvc', 'mvvm', 'clean architecture',
        'hexagonal', 'onion', 'ddd', 'domain driven', 'event driven', 'cqrs',
        'event sourcing', 'saga', 'orchestration', 'choreography', 'monolith',
        'microservices', 'service mesh', 'api gateway', 'bff', 'backend for frontend',
        
        # Integration & communication
        'integration', 'third-party', 'external', 'vendor', 'provider', 'sdk',
        'library', 'package', 'dependency', 'npm', 'composer', 'pip', 'maven',
        'email', 'smtp', 'sendgrid', 'mailgun', 'twilio', 'sms', 'push notification',
        'fcm', 'apns', 'stripe', 'paypal', 'braintree', 'square', 'payment gateway',
        
        # Mobile & cross-platform
        'mobile', 'ios', 'android', 'react native', 'flutter', 'ionic', 'cordova',
        'xamarin', 'native', 'hybrid', 'cross-platform', 'responsive', 'adaptive',
        
        # Data & analytics
        'analytics', 'tracking', 'metrics', 'kpi', 'reporting', 'dashboard',
        'business intelligence', 'bi', 'data warehouse', 'etl', 'data pipeline',
        'big data', 'hadoop', 'spark', 'kafka', 'stream processing', 'batch processing',
        'machine learning', 'ml', 'ai', 'artificial intelligence', 'nlp', 'computer vision',
        'recommendation', 'prediction', 'classification', 'clustering', 'neural network',
        
        # Version control & collaboration
        'git', 'github', 'gitlab', 'bitbucket', 'version control', 'vcs', 'scm',
        'branch', 'merge', 'pull request', 'code review', 'commit', 'tag', 'release',
        
        # Documentation
        'documentation', 'docs', 'api docs', 'swagger', 'openapi', 'readme',
        'wiki', 'confluence', 'jsdoc', 'javadoc', 'sphinx', 'mkdocs',

        # Modern backend & APIs
        'fastapi', 'django', 'rails', 'spring', 'express', 'nestjs', 'hapi',
        'falcon', 'phoenix', 'rpc', 'openapi spec',

        # Data & storage
        'snowflake', 'bigquery', 'redshift', 'clickhouse', 'timescaledb',
        'neo4j', 'graphdb', 'vector database', 'pinecone', 'weaviate', 'milvus',

        # Observability & monitoring
        'otel', 'opentelemetry', 'jaeger', 'zipkin', 'grafana loki', 'fluentd',
        'vector logs', 'elastic apm',

        # AI/ML
        'tensorflow', 'pytorch', 'transformers', 'huggingface', 'scikit-learn',
        'xgboost', 'lightgbm', 'mlflow', 'langchain', 'rag', 'llm',
        'fine-tuning', 'embedding', 'vector search', 'retrieval',

        # Networking & protocols
        'dns', 'http', 'http2', 'http3', 'quic', 'tcp', 'udp', 'grpc-web',
        'mqtt', 'coap', 'signalr',

        # Edge & IoT
        'edge computing', 'fog computing', 'iot', 'mqtt', 'coap', 'device provisioning',
        'sensor data', 'telemetry',

        # Build & tooling
        'bazel', 'nix', 'gradle kotlin', 'monorepo', 'lerna', 'turborepo',

        # Containers & orchestration
        'podman', 'cri-o', 'service mesh', 'istio', 'linkerd', 'cilium',

        # Security extensions
        'oidc', 'fido2', 'passkey', 'zero trust', 'secrets manager', 'vault'
    ]
    
    SETUP_KEYWORDS = [
        # Project initialization
        'setup', 'initialization', 'init', 'bootstrap', 'scaffold', 'boilerplate',
        'starter', 'template', 'skeleton', 'foundation', 'base', 'kickstart',
        
        # Configuration
        'configuration', 'config', 'settings', 'preferences', 'options', 'parameters',
        'environment', 'env', 'variables', 'constants', 'properties', 'yaml', 'json',
        'toml', 'ini', 'xml', 'dotenv', '.env', 'configure',
        
        # Installation & dependencies
        'installation', 'install', 'dependency', 'dependencies', 'package', 'library',
        'module', 'plugin', 'extension', 'npm', 'yarn', 'pnpm', 'composer', 'pip',
        'maven', 'gradle', 'bundler', 'gem', 'cargo', 'go mod', 'requirements',
        'package.json', 'composer.json', 'pom.xml', 'build.gradle',
        
        # Project structure
        'structure', 'architecture', 'organization', 'layout', 'hierarchy', 'folder',
        'directory', 'file structure', 'project structure', 'codebase', 'repository',
        
        # Development environment
        'environment', 'dev environment', 'local', 'development', 'staging', 'production',
        'workspace', 'ide', 'editor', 'vscode', 'intellij', 'eclipse', 'vim',
        'docker', 'vagrant', 'virtual machine', 'vm', 'container',
        
        # Build & tooling
        'build', 'compile', 'transpile', 'bundle', 'webpack', 'vite', 'rollup',
        'parcel', 'esbuild', 'babel', 'typescript', 'preprocessor', 'postprocessor',
        'linter', 'eslint', 'prettier', 'formatter', 'code style', 'editorconfig',
        
        # Version control setup
        'git init', 'repository', 'repo', 'clone', 'fork', '.gitignore', '.gitattributes',
        'branch strategy', 'workflow', 'gitflow', 'trunk-based',
        
        # Database setup
        'database setup', 'schema', 'migration', 'seed', 'seeder', 'fixture',
        'initial data', 'sample data', 'test data', 'dump', 'backup',
        
        # Infrastructure setup
        'infrastructure', 'provisioning', 'terraform', 'cloudformation', 'ansible',
        'chef', 'puppet', 'salt', 'server', 'hosting', 'domain', 'dns', 'ssl'

        # Infra & provisioning
        'kustomize', 'packer', 'vault', 'secrets manager', 'service account',
        'key rotation', 'certificate manager',

        # CI/CD & automation
        'makefile', 'justfile', 'taskfile', 'pipeline config',
        'pre-commit hooks', 'husky', 'lint-staged',

        # Developer tooling
        'devcontainer', 'codespaces', 'remote container', 'wsl2',
        'dotfiles', 'shell script', 'bashrc', 'zshrc',

        # Security setup
        'csp headers', 'rate limiting', 'api key rotation',
        'firewall rules', 'vpn setup', 'ssh config', 'tls certs',

        # Performance setup
        'cache warming', 'seeding strategy', 'load test config',
        'benchmark suite', 'profiling'
    ]
    
    PRIORITY_INDICATORS = {
        'high': [
            # Urgency & importance
            'critical', 'crucial', 'vital', 'essential', 'mandatory', 'required',
            'must', 'must have', 'must-have', 'necessary', 'needed', 'urgent',
            'immediate', 'asap', 'priority', 'high priority', 'top priority',
            
            # Core & fundamental
            'core', 'fundamental', 'basic', 'primary', 'main', 'principal',
            'key', 'major', 'central', 'pivotal', 'cornerstone', 'foundation',
            'baseline', 'base', 'root', 'primary',
            
            # Impact & significance
            'important', 'significant', 'substantial', 'material', 'considerable',
            'notable', 'critical path', 'blocking', 'blocker', 'dependency',
            'prerequisite', 'required for', 'needed for', 'before',
            
            # Business value
            'revenue', 'profit', 'business critical', 'customer facing', 'user facing',
            'compliance', 'regulatory', 'legal', 'security', 'safety', 'risk',

            # SLA & uptime
            'sla', 'uptime', 'downtime', 'outage', 'breach', 'regulatory required',
            'penalty', 'legal risk', 'blocking release', 'security patch',
            'customer deadline', 'launch blocker'
        ],
        'medium': [
            # Recommendation
            'should', 'should have', 'recommended', 'suggested', 'advised',
            'encouraged', 'preferred', 'desirable', 'beneficial', 'valuable',
            
            # Standard practice
            'standard', 'typical', 'common', 'usual', 'normal', 'conventional',
            'traditional', 'established', 'accepted', 'best practice',
            
            # Improvement
            'improve', 'improvement', 'enhance', 'enhancement', 'optimize',
            'optimization', 'refine', 'refinement', 'upgrade', 'update',
            'modernize', 'streamline', 'simplify',
            
            # Support & maintenance
            'support', 'maintain', 'maintenance', 'sustain', 'upkeep', 'care',
            'helpful', 'useful', 'handy', 'convenient', 'practical', 'functional',
            
            # Quality
            'quality', 'reliability', 'stability', 'robustness', 'durability',
            'consistency', 'accuracy', 'precision',
            
            # Business value
            'best effort', 'preferred option', 'industry standard', 'competitive advantage',
            'user request', 'feature parity', 'maintenance window'

        ],
        'low': [
            # Optional & nice-to-have
            'optional', 'nice to have', 'nice-to-have', 'would be nice', 'bonus',
            'extra', 'additional', 'supplementary', 'complementary', 'auxiliary',
            
            # Future & later
            'future', 'later', 'eventual', 'eventually', 'someday', 'down the road',
            'phase 2', 'phase 3', 'v2', 'v3', 'next version', 'future version',
            'backlog', 'wishlist', 'roadmap', 'long term', 'long-term',
            
            # Enhancement & polish
            'enhancement', 'polish', 'cosmetic', 'aesthetic', 'visual', 'styling',
            'ui polish', 'ux polish', 'refinement', 'tweak', 'adjustment',
            
            # Experimental & exploratory
            'experimental', 'exploratory', 'research', 'investigate', 'explore',
            'prototype', 'proof of concept', 'poc', 'spike', 'trial', 'test',
            
            # Minor improvements
            'minor', 'small', 'trivial', 'simple', 'easy', 'quick', 'fast',
            'incremental', 'marginal', 'slight', 'minimal',

            # Experimental & exploratory
            'experiment', 'ab test', 'try out', 'beta feature', 'alpha version',
            'candidate', 'sandbox', 'mockup', 'demo', 'sample', 'non-production'
        ]
    }
    
    def __init__(self, name: str, description: str, language: str = 'en'):
        self.name = name
        self.description = description.lower()
        self.language = language
        self.sentences = self._split_sentences(description)
        
    def _split_sentences(self, text: str) -> list:
        """Split text into sentences for analysis"""
        # Split by common sentence delimiters
        sentences = re.split(r'[.!?\n]+', text)
        return [s.strip() for s in sentences if s.strip()]
    
    def _extract_keywords(self, text: str, keywords: list) -> list:
        """Extract matching keywords from text"""
        text_lower = text.lower()
        found = []
        for keyword in keywords:
            if keyword in text_lower:
                found.append(keyword)
        return found
    
    def _determine_priority(self, text: str) -> str:
        """Determine priority based on text content"""
        text_lower = text.lower()
        scores = {'high': 0, 'medium': 0, 'low': 0}
        
        for priority, indicators in self.PRIORITY_INDICATORS.items():
            for indicator in indicators:
                if indicator in text_lower:
                    scores[priority] += 1
        
        # Return highest scoring priority, default to medium
        max_score = max(scores.values())
        if max_score == 0:
            return 'medium'
        
        for priority, score in scores.items():
            if score == max_score:
                return priority
        return 'medium'
    
    def _estimate_hours(self, complexity: str, has_subtasks: bool = False) -> int:
        """Estimate hours based on complexity"""
        base_estimates = {
            'high': 16,
            'medium': 8,
            'low': 4
        }
        estimate = base_estimates.get(complexity, 8)
        if has_subtasks:
            estimate = int(estimate * 1.5)
        return estimate
    
    def _extract_features(self) -> list:
        """Extract feature-related items from description"""
        features = []
        seen_features = set()
        
        for sentence in self.sentences:
            if any(kw in sentence.lower() for kw in self.FEATURE_KEYWORDS):
                # Clean and extract feature name
                feature = sentence[:100].strip()  # Limit length
                if feature and len(feature) > 10:
                    # Check for duplicates using normalized text
                    normalized = ' '.join(feature.lower().split())
                    
                    # Skip if too similar to existing feature
                    is_duplicate = False
                    for seen in seen_features:
                        if self._calculate_similarity(normalized, seen) > 0.7:
                            is_duplicate = True
                            break
                    
                    if not is_duplicate:
                        features.append({
                            'text': feature,
                            'priority': self._determine_priority(sentence)
                        })
                        seen_features.add(normalized)
        
        return features
    
    def _calculate_similarity(self, text1: str, text2: str) -> float:
        """Calculate similarity between two texts (0-1)"""
        words1 = set(text1.split())
        words2 = set(text2.split())
        if not words1 or not words2:
            return 0.0
        intersection = words1.intersection(words2)
        union = words1.union(words2)
        return len(intersection) / len(union) if union else 0.0
    
    def _extract_technical_components(self) -> list:
        """Extract technical components mentioned"""
        components = []
        for sentence in self.sentences:
            found_tech = self._extract_keywords(sentence, self.TECH_KEYWORDS)
            if found_tech:
                for tech in found_tech:
                    components.append({
                        'name': tech,
                        'context': sentence[:100],
                        'priority': self._determine_priority(sentence)
                    })
        return components
    
    def generate_tasks(self) -> dict:
        """Generate comprehensive task breakdown"""
        tasks = []
        
        # 1. Project Setup & Infrastructure (High Priority)
        setup_task = self._generate_setup_task()
        if setup_task:
            tasks.append(setup_task)
        
        # 2. Core Features (High to Medium Priority)
        feature_tasks = self._generate_feature_tasks()
        tasks.extend(feature_tasks)
        
        # 3. Technical Implementation (Medium Priority)
        tech_tasks = self._generate_technical_tasks()
        tasks.extend(tech_tasks)
        
        # 4. Testing & Quality Assurance (Medium Priority)
        qa_task = self._generate_qa_task()
        if qa_task:
            tasks.append(qa_task)
        
        # 5. Deployment & Documentation (Low to Medium Priority)
        deploy_task = self._generate_deployment_task()
        if deploy_task:
            tasks.append(deploy_task)
        
        return {"tasks": tasks}
    
    def _generate_setup_task(self) -> dict:
        """Generate project setup and initialization task"""
        has_setup = any(kw in self.description for kw in self.SETUP_KEYWORDS)
        
        subtasks = [
            {
                "title": "Project structure setup",
                "description": "Initialize project structure, dependencies, and configuration files",
                "priority": "high",
                "estimate_hours": 3,
                "subtasks": []
            },
            {
                "title": "Development environment configuration",
                "description": "Set up local development environment, tools, and workflows",
                "priority": "high",
                "estimate_hours": 2,
                "subtasks": []
            },
            {
                "title": "Version control and branching strategy",
                "description": "Initialize Git repository and define branching workflow",
                "priority": "medium",
                "estimate_hours": 1,
                "subtasks": []
            }
        ]
        
        return {
            "title": f"{self.name} - Project Setup & Infrastructure",
            "description": "Initialize project foundation, development environment, and core infrastructure",
            "priority": "high",
            "estimate_hours": 8,
            "subtasks": subtasks
        }
    
    def _generate_feature_tasks(self) -> list:
        """Generate tasks for identified features"""
        features = self._extract_features()
        tasks = []
        seen_task_titles = set()
        
        # Group features by priority
        by_priority = defaultdict(list)
        for feature in features:
            by_priority[feature['priority']].append(feature)
        
        # Generate tasks in priority order
        for priority in ['high', 'medium', 'low']:
            priority_features = by_priority.get(priority, [])
            if priority_features:
                for idx, feature in enumerate(priority_features[:3]):  # Limit to 3 per priority to reduce duplication
                    # Create a more concise title
                    title = f"Implement: {feature['text'][:60]}"
                    title_normalized = ' '.join(title.lower().split())
                    
                    # Skip if similar title already exists
                    is_duplicate = False
                    for seen_title in seen_task_titles:
                        if self._calculate_similarity(title_normalized, seen_title) > 0.8:
                            is_duplicate = True
                            break
                    
                    if not is_duplicate:
                        task = {
                            "title": title,
                            "description": feature['text'],
                            "priority": priority,
                            "estimate_hours": self._estimate_hours(priority, True),
                            "subtasks": self._generate_feature_subtasks(feature, priority)
                        }
                        tasks.append(task)
                        seen_task_titles.add(title_normalized)
        
        # If no specific features found, create generic feature task
        if not tasks:
            tasks.append({
                "title": f"{self.name} - Core Features Implementation",
                "description": "Implement main features and functionality as described in requirements",
                "priority": "high",
                "estimate_hours": 16,
                "subtasks": [
                    {"title": "Requirements analysis", "description": "Analyze and document detailed requirements", "priority": "high", "estimate_hours": 4, "subtasks": []},
                    {"title": "Feature design", "description": "Design feature architecture and user flows", "priority": "high", "estimate_hours": 4, "subtasks": []},
                    {"title": "Implementation", "description": "Develop core features", "priority": "high", "estimate_hours": 8, "subtasks": []}
                ]
            })
        
        return tasks
    
    def _generate_feature_subtasks(self, feature: dict, priority: str) -> list:
        """Generate subtasks for a feature - consolidated to avoid duplication"""
        # Only generate subtasks for high-priority features to reduce clutter
        if priority != 'high':
            return []
        
        subtasks = [
            {
                "title": "Design & Implementation",
                "description": f"Design architecture and implement: {feature['text'][:50]}",
                "priority": priority,
                "estimate_hours": 6,
                "subtasks": []
            },
            {
                "title": "Testing & Integration",
                "description": "Test and integrate with existing system",
                "priority": priority,
                "estimate_hours": 3,
                "subtasks": []
            }
        ]
        return subtasks
    
    def _generate_technical_tasks(self) -> list:
        """Generate tasks for technical components"""
        components = self._extract_technical_components()
        tasks = []
        
        # Group by component type and deduplicate
        component_groups = defaultdict(list)
        for comp in components:
            component_groups[comp['name']].append(comp)
        
        # Generate tasks for key technical areas (limit to avoid duplication)
        tech_areas = {
            'authentication': 'User Authentication & Authorization',
            'api': 'API Development & Integration',
            'database': 'Database Design & Implementation',
            'security': 'Security Implementation',
        }
        
        for key, title in tech_areas.items():
            if key in component_groups:
                priority = component_groups[key][0]['priority']
                tasks.append({
                    "title": title,
                    "description": f"Implement {title.lower()} for the project",
                    "priority": priority,
                    "estimate_hours": self._estimate_hours(priority),
                    "subtasks": []  # No subtasks to reduce duplication
                })
        
        return tasks
    
    def _generate_qa_task(self) -> dict:
        """Generate testing and QA task"""
        return {
            "title": "Testing & Quality Assurance",
            "description": "Comprehensive testing, bug fixes, and quality assurance",
            "priority": "medium",
            "estimate_hours": 12,
            "subtasks": []  # Simplified to reduce duplication
        }
    
    def _generate_deployment_task(self) -> dict:
        """Generate deployment and documentation task"""
        return {
            "title": "Deployment & Documentation",
            "description": "Prepare for production deployment and create documentation",
            "priority": "low",
            "estimate_hours": 8,
            "subtasks": []  # Simplified to reduce duplication
        }


def generate_plan(name: str, description: str, language: str):
    """Generate intelligent task breakdown from project description"""
    analyzer = TaskAnalyzer(name, description, language)
    return analyzer.generate_tasks()


def main():
    raw = sys.stdin.read()
    try:
        payload = json.loads(raw) if raw else {}
    except Exception:
        payload = {}

    action = payload.get('action')
    if action == 'detect_language':
        text = payload.get('text', '')
        lang = detect_language(text)
        print(json.dumps({"language": lang}))
        return

    if action == 'generate_plan':
        name = payload.get('project_name', 'Project')
        desc = payload.get('description', '')
        lang = payload.get('language', 'en')
        out = generate_plan(name, desc, lang)
        print(json.dumps(out))
        return

    print(json.dumps({"error": "unknown_action"}))

if __name__ == '__main__':
    main()
