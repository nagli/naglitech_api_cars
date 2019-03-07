# naglitech_api_cars
This is a demo API to access a database with an car table. 
The API has one endpoint, cars, and CRUD functions. 
A user can consume the following:
GET {URL}/v1/cars/
*Get a list of all cars in the table
GET {URL}/v1/cars/?make={}&model={}
*Get a list of cars by make and model, or either make or model 
GET {URL}/v1/cars/{id}
*Get a car by an id
DELETE {URL}/v1/cars/{id}
*Delete a car by an id
POST {URL}/v1/cars/  Body{"make":"", "model":"", "platform":""}
*Add a car to the table
POST {URL}/v1/cars/  Body[{"make":"", "model":"", "platform":""}, {}, {}]
*Add multiple cars to the table
PATCH {URL}/v1/cars/{id}  Body{"make":"", "model":"", "platform":""}
*Update a car
PUT {URL}/v1/cars/{id}  Body{"make":"", "model":"", "platform":""}
*Overwrite a car or if the car does not exist create it.
