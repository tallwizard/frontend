import React, { Component } from "react";
import Highlighter from "react-highlight-words";
import AsyncSelect from "react-select/async";

const highlighterClass = {
	backgroundColor: "yellow",
};

export default class AutoComplete extends Component {
	constructor(props) {
		super(props);
		this.state = {
			multiple: props.multiple,
			urlSearch: props.urlSearch,
		};
	}

	async fetchData(e) {
		const data = await axios
			.get(this.state.urlSearch + e)
			.then((res) => {
				return res.data;
			})
			.catch((err) => console.error(err.response));
		return data;
	}

	handleChange(event) {
		if (this.state.multiple === true && typeof event === "object") {
			var data = [];
			event.filter((element) => {
				data.push(element.value);
			});
		} else {
			var data = event.value;
		}

		this.props.results(data, true);
	}

	formatOptionLabel({ label }, { inputValue }) {
		return (
			<Highlighter
				searchWords={[inputValue]}
				highlightStyle={highlighterClass}
				textToHighlight={label}
			/>
		);
	}

	render() {
		return (
			<>
				<AsyncSelect
					cacheOptions
					defaultOptions
					loadOptions={this.fetchData.bind(this)}
					isLoading={false}
					onChange={this.handleChange.bind(this)}
					placeholder=" "
					isMulti={this.state.multiple}
					noOptionsMessage={() => "No coinciden los datos"}
					loadingMessage={() => "Buscando..."}
					formatOptionLabel={this.formatOptionLabel}
				/>
			</>
		);
	}
}
