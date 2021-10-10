import React, {Component} from 'react'
import axios from "axios";

export default class App extends Component {

    componentDidMount() {
        axios.get("/sanctum/csrf-cookie").then(response => {
            axios.post("/api/login", {
                username: "ian",
                password: "test123"
            })
        })
    }

    render() {
        return (
            <div>
                <h3>Hi!!!</h3>
            </div>
        )
    }
}
