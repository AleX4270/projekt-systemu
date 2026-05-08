# Build

build-backend:
	docker build -f ./backend/Dockerfile -t ghcr.io/alex4270/system-project-backend:latest --platform linux/amd64 .

build-frontend:
	docker build --build-arg CONFIG="production" -f ./frontend/Dockerfile -t ghcr.io/alex4270/system-project-frontend:latest --platform linux/amd64 .

# Push

push-backend:
	docker push ghcr.io/alex4270/system-project-backend:latest

push-frontend:
	docker push ghcr.io/alex4270/system-project-frontend:latest
