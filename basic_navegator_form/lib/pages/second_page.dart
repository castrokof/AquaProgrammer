import 'package:flutter/material.dart';

class SecondPage extends StatelessWidget {
  const SecondPage({Key key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    SecondPageArguments arguments = ModalRoute.of(context).settings.arguments;

    return Scaffold(
      appBar: AppBar(
        title: Text("Segunda Pantalla"),
      ),
      body: Center(
        child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              Text(arguments.name),
              Text(arguments.lastname),
            ]),
      ),
    );
  }
}

class SecondPageArguments {
  String name, lastname;
  SecondPageArguments({this.name, this.lastname});
}
