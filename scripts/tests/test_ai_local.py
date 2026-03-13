#!/usr/bin/env python3
"""Test the enhanced AI task generator locally"""
import json
import subprocess

def test_language_detection():
    """Test language detection"""
    payload = {
        "action": "detect_language",
        "text": "Build a multilingual blog platform with authentication"
    }
    
    result = subprocess.run(
        ['python3', 'scripts/ai_task_gen.py'],
        input=json.dumps(payload),
        capture_output=True,
        text=True,
        cwd='/opt/lampp/htdocs/project-management'
    )
    
    print("=== Language Detection Test ===")
    print(f"Input: {payload['text']}")
    print(f"Output: {result.stdout}")
    print(f"Errors: {result.stderr}")
    print()

def test_task_generation():
    """Test task generation with a complex description"""
    payload = {
        "action": "generate_plan",
        "project_name": "E-Commerce Platform",
        "description": """
        Build a comprehensive e-commerce platform with the following core features:
        - User authentication and authorization with role-based access
        - Product catalog with search and filtering
        - Shopping cart and checkout system
        - Payment integration with multiple gateways
        - Order management and tracking
        - Admin dashboard for inventory management
        - Customer reviews and ratings
        - Email notifications for orders
        - RESTful API for mobile apps
        - Performance optimization and caching
        - Security implementation including HTTPS and data encryption
        - Deployment on cloud infrastructure with CI/CD pipeline
        """,
        "language": "en"
    }
    
    result = subprocess.run(
        ['python3', 'scripts/ai_task_gen.py'],
        input=json.dumps(payload),
        capture_output=True,
        text=True,
        cwd='/opt/lampp/htdocs/project-management'
    )
    
    print("=== Task Generation Test ===")
    print(f"Project: {payload['project_name']}")
    print(f"Output:")
    
    if result.stdout:
        try:
            tasks = json.loads(result.stdout)
            print(json.dumps(tasks, indent=2))
            
            # Summary
            if 'tasks' in tasks:
                print(f"\n=== Summary ===")
                print(f"Total tasks generated: {len(tasks['tasks'])}")
                for i, task in enumerate(tasks['tasks'], 1):
                    subtask_count = len(task.get('subtasks', []))
                    print(f"{i}. {task['title']} (Priority: {task['priority']}, Subtasks: {subtask_count})")
        except json.JSONDecodeError:
            print(result.stdout)
    
    if result.stderr:
        print(f"\nErrors: {result.stderr}")
    print()

if __name__ == '__main__':
    test_language_detection()
    test_task_generation()
