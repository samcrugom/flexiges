FROM node:22-alpine

WORKDIR /app

# Copiar package.json primero (caché de capas)
COPY package*.json ./

RUN npm install

# Copiar resto del código
COPY . .

EXPOSE 5173
