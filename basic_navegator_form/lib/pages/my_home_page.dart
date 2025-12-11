import 'package:flutter/material.dart';

import 'second_page.dart';

class MyHomePage extends StatefulWidget {
  @override
  _MyHomePageState createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  String nameValue, lastnameValue;
  // Se crea el controlador porque cuando se usan listview y se ocultan los campos se borran los datos ingresados por tal motvio se recomienda usar controladores

  TextEditingController nameController;
  TextEditingController lastnameController;

  final formKey = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: AppBar(
          title:
              Text("Implementacion de campo de formulario sin el Widget Form"),
        ),
        body: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Form(
            key: formKey,
            child: ListView(children: <Widget>[
              TextFormField(
                controller: nameController,
                decoration: InputDecoration(labelText: "Nombre"),
                onSaved: (value) {
                  nameValue = value;
                },
                validator: (value) {
                  if (value.isEmpty) {
                    return "Campo requerido";
                  }
                },
              ),
              TextFormField(
                controller: lastnameController,
                decoration: InputDecoration(labelText: "Apellido"),
                onSaved: (value) {
                  lastnameValue = value;
                },
                validator: (value) {
                  if (value.isEmpty) {
                    return "Campo requerido";
                  }
                },
              ),
              TextFormField(
                decoration: InputDecoration(labelText: "Telefono"),
                keyboardType: TextInputType.phone,
              ),
              TextFormField(
                decoration: InputDecoration(labelText: "Ingrese el correo"),
                keyboardType: TextInputType.emailAddress,
              ),
              TextFormField(
                decoration: InputDecoration(labelText: "Ingrese edad"),
                keyboardType: TextInputType.number,
              ),
              RaisedButton(
                child: Text("Mostrar segunda pantalla"),
                onPressed: () {
                  _showSecondPage(context);
                },
              ),
            ]),
          ),
        ));
  }

  void _showSecondPage(BuildContext context) {
    if (formKey.currentState.validate()) {
      formKey.currentState.save();

      Navigator.of(context).pushNamed("/second",
          arguments: SecondPageArguments(
              name: this.nameValue, lastname: this.lastnameValue));
    }
  }

  @override
  void initState() {
    super.initState();
    nameController = TextEditingController();
    lastnameController = TextEditingController();
  }

  @override
  void dispose() {
    // TODO: implement dispose
    super.dispose();
    this.nameController.dispose();
    this.lastnameController.dispose();
  }
}
