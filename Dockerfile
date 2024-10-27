FROM node:20-alpine

USER node
WORKDIR /home/node

RUN npm install --save-dev jest babel-jest @babel/core @babel/preset-env
#@babel/preset-typescript
#RUN npm install --save-dev ts-jest ts-node typescript

