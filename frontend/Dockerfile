# Build stage
FROM node:20-alpine AS build

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Build arguments for environment variables
ARG VITE_API_URL=http://localhost:8000/api
ARG VITE_PUSHER_APP_KEY=your-pusher-key
ARG VITE_PUSHER_APP_CLUSTER=mt1

# Set environment variables for build
ENV VITE_API_URL=$VITE_API_URL
ENV VITE_PUSHER_APP_KEY=$VITE_PUSHER_APP_KEY
ENV VITE_PUSHER_APP_CLUSTER=$VITE_PUSHER_APP_CLUSTER

# Build the application
RUN npm run build

# Production stage
FROM nginx:alpine

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copy built files
COPY --from=build /app/dist /usr/share/nginx/html

# Expose port
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
