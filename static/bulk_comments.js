const inputs = Array.prototype.slice.call(document.querySelectorAll('.comment textarea'));
inputs.forEach(input=>{
	input.addEventListener('change',function() {
		const value = this.value;
		const key = this.getAttribute('name');
		const data = {
			key: key,
			comment: value
		};
		this.classList.add('changed');

		fetch('',{
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(data)
		})
		.then(response => {
			input.classList.remove('changed');
		});
	});
});

const preview = document.getElementById('preview');
const links = document.querySelectorAll('a.source');
for(let l of links) {
    console.log(l);
    l.addEventListener('click',e => {
        preview.setAttribute('src',l.getAttribute('href'));
        e.stopPropagation();
        e.preventDefault();
    });
}
